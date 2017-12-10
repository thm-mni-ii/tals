//
//  LoginViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 05.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "LoginViewController.h"

@interface LoginViewController ()

@end

@implementation LoginViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    // Do any additional setup after loading the view.
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

/*
#pragma mark - Navigation

// In a storyboard-based application, you will often want to do a little preparation before navigation
- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    // Get the new view controller using [segue destinationViewController].
    // Pass the selected object to the new view controller.
}
*/

- (BOOL)textFieldShouldReturn:(UITextField *)theTextField {
    if (theTextField == self.Password) {
        [theTextField resignFirstResponder];
    } else if (theTextField == self.UserName) {
        [self.Password becomeFirstResponder];
    }
    return YES;
}

- (IBAction)signIn:(id)sender {
    NSURL *url = [NSURL URLWithString:@"https://cas.thm.de:443/cas/"];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSString *search = @"LT-";
    NSString *sub = [ret substringFromIndex:NSMaxRange([ret rangeOfString:search])];
    NSString *sub2 = [sub substringToIndex:37];
    
    NSLog(@"lt=LT-%@", sub2);
}

- (IBAction)uName:(id)sender {
}

- (IBAction)pWord:(id)sender {
    
}

- (void) loginCAS {
    
}
@end
