//
//  LoginViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 05.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "LoginViewController.h"
#import "AppData.h"

@interface LoginViewController ()

@property(nonatomic, strong) NSURLConnection *connection;

@end

@implementation LoginViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    self.loading.hidden = YES;
    
    //setup aditional keyboard buttons
    [self setupInputAccessoryViews];
    // monitor which field is currently selected, and set each of the input accessory views.
    for(UITextField *field in _textFields) {
        [field addTarget:self action:@selector(setActiveTextField:) forControlEvents:UIControlEventEditingDidBegin];
        [field setInputAccessoryView:[_inputAccessoryViews objectAtIndex:field.tag]];
    }
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

// Login Process
- (void)doLogin {
    // Request Token from server
    username = self.UserName.text;
    password = self.Password.text;
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    
    // If the Switch is set, stay logged in
    if (self.switchWay.isOn) {
        [defaults setBool:YES forKey:@"checkLogged"];
    }
    
    // Testuser with test data
    if ([username isEqualToString:@"testuser"] &&
        [password isEqualToString:@"kl1J9fdX"]) {
        [defaults setValue:@"91" forKey:@"userID"];
        [defaults setValue:@"2d4e2fc785ac5506235c7901ca5e403e" forKey:@"token"];
        [defaults synchronize];
        [self dismissViewControllerAnimated:YES completion:nil];
    } else {
        // Check Connection
        if ([AppData checkConnection]) {
            NSTimer *t = [NSTimer scheduledTimerWithTimeInterval:10.0
                                                          target:self
                                                        selector:@selector(loginTimeoutHandler:)
                                                        userInfo:nil
                                                         repeats:NO];
            self.UserName.hidden = YES;
            self.Password.hidden = YES;
            self.switchWay.hidden = YES;
            self.stayLogged.hidden = YES;
            self.logIn.hidden = YES;
            self.loading.hidden = NO;
            [AppData
             getToken:username
             password:password
             token:^(TokenObject *token) {
                 if (token.checkLogged) {
                     [t invalidate];
                     [self dismissViewControllerAnimated:YES completion:nil];
                 } else {
                     [t invalidate];
                     [self onError:@"Passwort oder Nutzername falsch."];
                 }
             }
             err:^(NSError *error) {
                 [t invalidate];
                 [self onError:@"Passwort oder Nutzername falsch."];
             }];
        } else {
            [self onError:@"Es kann keine Verbindung zum Server aufgebaut werden."];
        }
    }
}

- (void)loginTimeoutHandler:(NSTimer *)timer {
    [self onError:@"Zeitüberschreitung beim Verbindungsaufbau zum Server."];
}

// Display an error message and show the login fields
- (void)onError:(NSString *) message {
    // show the error message
    UIAlertController *alertController = [UIAlertController
                                          alertControllerWithTitle:@"Fehler"
                                          message: message
                                          preferredStyle:UIAlertControllerStyleAlert];
    [alertController
     addAction:[UIAlertAction actionWithTitle:@"OK"
                                        style:UIAlertActionStyleDefault
                                      handler:^(UIAlertAction *action){
                                      }]];
    [self presentViewController:alertController animated:YES completion:nil];
    
    // hide the loading message and unhide the login fields
    self.UserName.hidden = NO;
    self.Password.hidden = NO;
    self.switchWay.hidden = NO;
    self.stayLogged.hidden = NO;
    self.logIn.hidden = NO;
    self.loading.hidden = YES;
}

//
// Controls behavior of touching the Next or Go button in the keyboard.
//
// On touching the Next button when in the Username field, input switches to password.
// The Go Button when in the password field starts the login process
//
- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    // if currently focused on first text field, go to the next text field
    if (textField.tag == 0) {
        [self.Password becomeFirstResponder];
    // if currently focused on last text field, dismiss the keyboard.
    } else if (textField.tag == 1) {
        [[_textFields objectAtIndex:textField.tag] resignFirstResponder];
        [self doLogin];
    }
    
    return YES;
}

- (void)setActiveTextField:(UITextField *)activeTextField {
    _activeTextField = activeTextField;
}
//
// Focus on the previous UITextField
//
- (void)goToPrevField {
    [[_textFields objectAtIndex:(_activeTextField.tag - 1)] becomeFirstResponder];
}

//
// Focus on the next UITextField
//
- (void)goToNextField {
    [[_textFields objectAtIndex:(_activeTextField.tag + 1)] becomeFirstResponder];
}

//
// Dismiss the keyboard when done is tapped.
//
- (void)dismissKeyboard {
    [[_textFields objectAtIndex:_activeTextField.tag] resignFirstResponder];
}

//
// Create the 2 accessory views for each of the the UITextFields, each containing
// a previous button, a next button, and a done button.
//
- (void)setupInputAccessoryViews {
    _inputAccessoryViews = [[NSArray alloc] initWithObjects:[[UIToolbar alloc] init], [[UIToolbar alloc] init], [[UIToolbar alloc] init], [[UIToolbar alloc] init], nil];
    
    for(UIToolbar *accessoryView in _inputAccessoryViews) {
        UIBarButtonItem *prevButton  = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:101 target:nil action:@selector(goToPrevField)]; // 101 is the < character
        UIBarButtonItem *nextButton  = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:102 target:nil action:@selector(goToNextField)]; // 102 is the > character
        UIBarButtonItem *doneButton  = [[UIBarButtonItem alloc] initWithTitle:@"Done" style:UIBarButtonItemStylePlain target:nil action:@selector(dismissKeyboard)];
        UIBarButtonItem *flexSpace   = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil];
        UIBarButtonItem *placeholder = [[UIBarButtonItem alloc] initWithTitle:@"" style:UIBarButtonItemStylePlain target:nil action:nil];
        
        [accessoryView sizeToFit];
        [accessoryView setItems:[NSArray arrayWithObjects: prevButton, placeholder, nextButton, placeholder, flexSpace, placeholder, doneButton, nil] animated:YES];
    }
    
    // disable the previous button in the first accessory view
    ((UIBarButtonItem*)[((UIToolbar*)[_inputAccessoryViews objectAtIndex:0]).items objectAtIndex:0]).enabled = NO;
    // disable the next button in the last accessory view
    ((UIBarButtonItem*)[((UIToolbar*)[_inputAccessoryViews objectAtIndex:1]).items objectAtIndex:2]).enabled = NO;
}

//Action for the "Anmelden" (login) button
- (IBAction)signIn:(id)sender {
    [self doLogin];
}

//Action for the "Angemeldet bleiben" (stay logged in) button
- (IBAction)stayLoggedIn:(id)sender {
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    if (self.switchWay.isOn) {
        [defaults setBool:YES forKey:@"checkLogged"];
    } else {
        [defaults setBool:NO forKey:@"checkLogged"];
    }
}
@end

