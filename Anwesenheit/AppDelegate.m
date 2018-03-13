//
//  AppDelegate.m
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "AppDelegate.h"
#import "AppData.h"

@interface AppDelegate ()

@end

@implementation AppDelegate


- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions {
    // Override point for customization after application
    [[UINavigationBar appearance] setBarTintColor:[UIColor colorWithRed:128.0/255.0 green:186.0/255.0 blue:36.0/255.0 alpha:1.0]];
    [[UINavigationBar appearance] setTintColor:[UIColor whiteColor]];
    [[UINavigationBar appearance] setTitleTextAttributes:@{NSForegroundColorAttributeName : [UIColor whiteColor]}];
    // Remove UserData from the UserDefaults in Case the User does not wish to stay logged in
    return YES;
}


-(void) logout
{
    // Set Logout in UserDefaults
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    [defaults setBool:NO forKey:@"checkLogged"];
    
}


@end
