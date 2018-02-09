#import <foundation/Foundation.h>
#import "TokenObject.h"

@interface AppData : NSObject 

@property (nonatomic, assign) BOOL *sLogged;

+ (AppData *)SharedAppData;
+ (void) loginCAS:(NSString *)username Password:(NSString *)currentPassword;
+ (void)getToken:(NSString *)username password:(NSString *)password token:(void (^)(TokenObject *token))completionHandler;
+ (NSString *)getLT;
+ ( NSURLSession * )getURLSession;
+ (void) clearAllCookies;
+ (NSArray * )getClasses;
+ (BOOL) checkToken;
+ (BOOL) getPinActive:(int) appointmentID;
+ (BOOL) sendPIN:(NSString *) appointmentID pin:(NSString *) pin;
+ (int) getDaysAbsent:(int) appointmentID;


@end
