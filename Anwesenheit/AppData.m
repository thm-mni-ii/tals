#import "AppData.h"

@implementation AppData : NSObject

static AppData *shared = NULL;

@synthesize sLogged;

#pragma mark Singleton Methods

+ (AppData *)SharedAppData {
    static AppData *sharedAppData = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedAppData = [[self alloc] init];
    });
    return sharedAppData;
}

- (id)init {
    if (self = [super init]) {
        sLogged = NO;
    }
    return self;
}

- (void)dealloc {
    // Should never be called, but just here for clarity really.
}

@end
