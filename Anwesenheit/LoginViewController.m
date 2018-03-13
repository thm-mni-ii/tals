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

@property (nonatomic, strong) NSURLConnection *connection;

@end

@implementation LoginViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    self.loading.hidden = YES;
    // Do any additional setup after loading the view.
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

// Login Process
-(void) doLogin{
    // Request Token from server
    username = self.UserName.text;
    password = self.Password.text;
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    
    // If the Switch is set, stay logged in
    if(self.switchWay.isOn){
        [defaults setBool:YES forKey:@"checkLogged"];
    }
    
    // Testuser with test data
    if([username isEqualToString:@"testuser"] && [password isEqualToString:@"kl1J9fdX"]){
        [defaults setValue:@"91" forKey:@"userID"];
        [defaults setValue:@"2d4e2fc785ac5506235c7901ca5e403e" forKey:@"token"];
        [defaults synchronize];
        [self dismissViewControllerAnimated:YES completion:nil];
    }
    else{
        // Check Connection
        if([AppData checkConnection]){
            NSTimer *t = [NSTimer scheduledTimerWithTimeInterval: 10.0
                                                          target: self
                                                        selector:@selector(onTick:)
                                                        userInfo: nil repeats:NO];
            self.UserName.hidden = YES;
            self.Password.hidden = YES;
            self.switchWay.hidden = YES;
            self.stayLogged.hidden = YES;
            self.logIn.hidden = YES;
            self.loading.hidden = NO;
            [AppData getToken:username password:password token:^(TokenObject *token){
                if(token.checkLogged){
                    [t invalidate];
                    [self dismissViewControllerAnimated:YES completion:nil];
                }
        }];}
        // Display Error if Connection fails
        else{
            UIAlertController *alertController = [UIAlertController  alertControllerWithTitle:@"Fehler"  message:@"Es kann keine Verbindung zum Server aufgebaut werden."  preferredStyle:UIAlertControllerStyleAlert];
            [alertController addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
            }]];
            [self presentViewController:alertController animated:YES completion:nil];
    }
    }
}

// Timer, displays wrong Password/Username error after a while
-(void)onTick:(NSTimer *)timer {
    UIAlertController *alertController = [UIAlertController  alertControllerWithTitle:@"Fehler"  message:@"Passwort oder Nutzername falsch."  preferredStyle:UIAlertControllerStyleAlert];
    [alertController addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
    }]];
    [self presentViewController:alertController animated:YES completion:nil];
    self.UserName.hidden = NO;
    self.Password.hidden = NO;
    self.switchWay.hidden = NO;
    self.stayLogged.hidden = NO;
    self.logIn.hidden = NO;
    self.loading.hidden = YES;
}


// resigns Textfield
- (BOOL)textFieldShouldReturn:(UITextField *)theTextField {
    if (theTextField == self.Password) {
        [theTextField resignFirstResponder];
    } else if (theTextField == self.UserName) {
        [self.Password becomeFirstResponder];
    }
    return YES;
}

- (IBAction)signIn:(id)sender {
    [self doLogin];
}


- (IBAction)uName:(id)sender {
}

- (IBAction)pWord:(id)sender {
    [self doLogin];
}

- (IBAction)stayLoggedIn:(id)sender {
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    if(self.switchWay.isOn){
        [defaults setBool:YES forKey:@"checkLogged"];
    }
    else{
        [defaults setBool:NO forKey:@"checkLogged"];
    }
    
    
}
@end
