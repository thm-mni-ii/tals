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

+(void)setLogged{
    
}

+ (NSString *)getLT{
    NSURL *url = [NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://moodle.herwegh.me/login/index.php"];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSLog(@"%@",ret);
    NSString *search = @"LT-";
    NSString *sub2 = [[ret substringFromIndex:NSMaxRange([ret rangeOfString:search])] substringToIndex:37];
    NSString *lt = [NSString stringWithFormat:@"LT-%@", sub2];
    return lt;
}


+ (void) loginCAS {
    //extraction of Login Ticket
    NSString *lt = self.getLT;
    
    //LoginProcess
    NSString *post = [NSString stringWithFormat:@"username=crbr02&password=5WOVxq8B2lj5XZ6W6NyX&lt=%@&execution=e1s1&gateway=true&_eventId=submit&submit=Anmelden", lt];
    
    NSData *postData = [post dataUsingEncoding:NSASCIIStringEncoding allowLossyConversion:YES];
    
    NSString *postLength = [NSString stringWithFormat:@"%lu", ( unsigned long )[postData length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] init];
    // insert whatever URL you would like to connect to
    [request setURL:[NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://moodle.herwegh.me/login/index.php"]];
    
    [request setHTTPMethod:@"POST"];
    [request setValue:postLength forHTTPHeaderField:@"Content-Length"];
    [request setValue:@"application/x-www-form-urlencoded" forHTTPHeaderField:@"Content-Type"];
    [request setHTTPBody:postData];
    NSURLSessionDataTask *task = [[self getURLSession] dataTaskWithRequest:request completionHandler:^( NSData *data, NSURLResponse *response, NSError *error )
                                  {
                                      dispatch_async( dispatch_get_main_queue(),
                                                     ^{
                                                         // parse returned data
                                                         /*NSData *cookieData = [NSKeyedArchiver archivedDataWithRootObject:[[NSHTTPCookieStorage sharedHTTPCookieStorage] cookies]];
                                                         [[NSUserDefaults standardUserDefaults] setObject:cookieData forKey:@"ApplicationCookie"];
                                                         [[NSUserDefaults standardUserDefaults] synchronize];
                                                         if ([cookieData length] > 0) {
                                                             NSArray *cookies = [NSKeyedUnarchiver unarchiveObjectWithData:cookieData];
                                                             for (NSHTTPCookie *cookie in cookies) {
                                                                 [[NSHTTPCookieStorage sharedHTTPCookieStorage] setCookie:cookie];
                                                             }
                                                         }*/
                                                         NSURL *url = [NSURL URLWithString:@"https://moodle.herwegh.me/mod/tals/token.php"];
                                                         NSData *data = [NSData dataWithContentsOfURL:url];
                                                         NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
                                                         NSLog(@"%@",ret);
                                                         /*for (NSHTTPCookie *cookie in [[NSHTTPCookieStorage sharedHTTPCookieStorage] cookies])
                                                         {
                                                             NSLog(@"name: '%@'\n",   [cookie name]);
                                                             NSLog(@"value: '%@'\n",  [cookie value]);
                                                             NSLog(@"domain: '%@'\n", [cookie domain]);
                                                             NSLog(@"path: '%@'\n",   [cookie path]);
                                                         }
                                                         NSString *result = [[NSString alloc] initWithData:data encoding:NSASCIIStringEncoding];
                                                         NSLog( @"%@", result );
                                                         NSHTTPURLResponse *httpResponse = (NSHTTPURLResponse*)response;
                                                         if ([response respondsToSelector:@selector(allHeaderFields)]) {
                                                             NSDictionary *dictionary = [httpResponse allHeaderFields];
                                                             NSLog([dictionary description]);
                                                         }*/
                                                     } );
                                  }];
    
    
    [task resume];
    
}

+ ( NSURLSession * )getURLSession
{
    static NSURLSession *session = nil;
    static dispatch_once_t onceToken;
    dispatch_once( &onceToken,
                  ^{
                      NSURLSessionConfiguration *configuration = [NSURLSessionConfiguration defaultSessionConfiguration];
                      session = [NSURLSession sessionWithConfiguration:configuration];
                  } );
    
    return session;
}

- (void)dealloc {
    // Should never be called, but just here for clarity really.
}

@end
