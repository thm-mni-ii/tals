#import <foundation/Foundation.h>

@interface AppData : NSObject {
    NSString *someProperty;
}

@property (nonatomic, retain) NSString *someProperty;

+ (id)SharedAppData;
+ (BOOL) isLoggedIn;
+ (void) clearData;

@end
