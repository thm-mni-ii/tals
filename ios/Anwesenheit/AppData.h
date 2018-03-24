#import "TokenObject.h"
#import <foundation/Foundation.h>

@interface AppData : NSObject {
  NSString *URL;
}

@property(nonatomic, assign) BOOL *sLogged;

+ (AppData *)SharedAppData;
+ (void)loginCAS:(NSString *)username
        Password:(NSString *)currentPassword
         success:(void (^)(TokenObject *responseDict))success
         failure:(void (^)(NSError *error))failure;
+ (void)getToken:(NSString *)username
        password:(NSString *)password
           token:(void (^)(TokenObject *token))completionHandler
             err:(void (^)(NSError *err))errorHandler;
+ (NSString *)getLT;
+ (NSURLSession *)getURLSession;
+ (void)clearAllCookies;
+ (NSArray *)getAppointments;
+ (BOOL)checkToken;
+ (BOOL)sendPIN:(NSString *)appointmentID pin:(NSString *)pin;
+ (NSString *)getDaysAbsent:(id)appointmentID;
+ (NSArray *)getCourses;
+ (BOOL)checkConnection;
+ (NSData *)getData:(NSString *)function adValues:(NSString *) additionalValues;

@end
