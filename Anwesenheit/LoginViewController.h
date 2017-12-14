//
//  LoginViewController.h
//  Anwesenheit
//
//  Created by Sarah B on 05.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "TokenObject.h"

@interface LoginViewController : UIViewController{
    NSString *username;
    NSString *password;
    TokenObject *loginToken;
}
@property (weak, nonatomic) IBOutlet UITextField *UserName;
@property (weak, nonatomic) IBOutlet UITextField *Password;
- (IBAction)signIn:(id)sender;
- (IBAction)uName:(id)sender;
- (IBAction)pWord:(id)sender;

@end
