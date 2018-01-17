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
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    BOOL stayLogged = [defaults boolForKey:@"checkLogged"];
    if(!stayLogged){
        [defaults setValue:0 forKey:@"userID"];
        [defaults setValue:0 forKey:@"token"];
        [defaults setValue:0 forKey:@"externalService"];
        [defaults setValue:0 forKey:@"validTime"];
        [defaults synchronize];
    }
    BOOL logged = [AppData checkToken];
    //AppData *tmp = [AppData SharedAppData];
    if(!logged) {
        [self showLoginScreen:NO];
    }
    
    return YES;
}

-(void) showLoginScreen:(BOOL)animated
{
    
    // Get login screen from storyboard and present it
    UIStoryboard *storyboard = [UIStoryboard storyboardWithName:@"Main" bundle:nil];
    UIViewController *LoginViewController = [storyboard instantiateViewControllerWithIdentifier:@"loginScreen"];
    [self.window makeKeyAndVisible];
    [self.window.rootViewController presentViewController:LoginViewController
                                                 animated:animated
                                               completion:nil];
}

-(void) logout
{
    // Remove data from singleton (where all my app data is stored)
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    [defaults setBool:NO forKey:@"checkLogged"];
    
    // Reset view controller (this will quickly clear all the views)
    //UIStoryboard *storyboard = [UIStoryboard storyboardWithName:@"MainStoryboard" bundle:nil];
    //MainTabControllerViewController *viewController = (MainTabControllerViewController *)[storyboard instantiateViewControllerWithIdentifier:@"mainView"];
    //[self.window setRootViewController:;
    
    // Show login screen
    [self showLoginScreen:YES];
    
}

- (void)applicationWillResignActive:(UIApplication *)application {
    // Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
    // Use this method to pause ongoing tasks, disable timers, and invalidate graphics rendering callbacks. Games should use this method to pause the game.
}


- (void)applicationDidEnterBackground:(UIApplication *)application {
    // Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later.
    // If your application supports background execution, this method is called instead of applicationWillTerminate: when the user quits.
}


- (void)applicationWillEnterForeground:(UIApplication *)application {
    // Called as part of the transition from the background to the active state; here you can undo many of the changes made on entering the background.
}


- (void)applicationDidBecomeActive:(UIApplication *)application {
    // Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
}


- (void)applicationWillTerminate:(UIApplication *)application {
    // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
}


@end
