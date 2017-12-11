#import <foundation/Foundation.h>

@interface AppData : NSObject {
    BOOL *sLogged;
}

@property (nonatomic, assign) BOOL *sLogged;

+ (AppData *)SharedAppData;
+ (void) loginCAS;
+ (void) setCookies;
+ (NSString *)getLT;
+ ( NSURLSession * )getURLSession;

@end
