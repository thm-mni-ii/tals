#import <foundation/Foundation.h>

@interface AppData : NSObject {
    BOOL *sLogged;
}

@property (nonatomic, assign) BOOL *sLogged;

+ (AppData *)SharedAppData;
+ (void) setLogged;
+ (void) loginCAS:(NSString *)username Password:(NSString *)currentPassword;
+ (NSString *)getLT;
+ ( NSURLSession * )getURLSession;


@end
