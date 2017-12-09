#import "AppData.h"

@implementation AppData : NSObject

@synthesize someProperty;

#pragma mark Singleton Methods

+ (id)sharedAppData {
    static AppData *sharedAppData = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedAppData = [[self alloc] init];
    });
    return sharedAppData;
}

- (id)init {
    if (self = [super init]) {
        someProperty = @"Default Property Value";
    }
    return self;
}

+ (BOOL) isLoggedIn {
    return NO;
}

+ (void) clearData {
    return;
}

- (void)dealloc {
    // Should never be called, but just here for clarity really.
}

@end
