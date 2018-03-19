//
//  LoginViewController.h
//  Anwesenheit
//
//  Created by Sarah B on 05.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "TokenObject.h"
#import <UIKit/UIKit.h>

@interface LoginViewController : UIViewController {
  NSString *username;
  NSString *password;
  TokenObject *loginToken;
}
@property(weak, nonatomic) IBOutlet UITextField *UserName;
@property(weak, nonatomic) IBOutlet UISwitch *switchWay;
@property(weak, nonatomic) IBOutlet UITextField *Password;
@property(weak, nonatomic) IBOutlet UIButton *logIn;
@property(weak, nonatomic) IBOutlet UILabel *stayLogged;
- (IBAction)signIn:(id)sender;
- (IBAction)stayLoggedIn:(id)sender;
@property(weak, nonatomic) IBOutlet UILabel *loading;

@end
