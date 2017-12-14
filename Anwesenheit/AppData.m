#import "AppData.h"
#import "TokenObject.h"

@implementation AppData : NSObject

static AppData *shared = NULL;


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
    }
    return self;
}

+ (NSString *)getLT{
    NSURL *url = [NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://moodle.herwegh.me/login/index.php"];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSString *search = @"LT-";
    NSString *sub2 = [[ret substringFromIndex:NSMaxRange([ret rangeOfString:search])] substringToIndex:37];
    NSString *lt = [NSString stringWithFormat:@"LT-%@", sub2];
    return lt;
}


+ (void) loginCAS:(NSString *)username Password:(NSString *)currentPassword success:(void (^)(TokenObject *responseDict))success failure:(void(^)(NSError* error))failure {
    
    TokenObject *currentToken = nil;
    if (!currentToken) {
        [self clearAllCookies];
    }
    //extraction of Login Ticket
    NSString *lt = self.getLT;
    
    //LoginProcess
    NSString *post = [NSString stringWithFormat:@"username=%@&password=%@&lt=%@&execution=e1s1&gateway=true&_eventId=submit&submit=Anmelden", username, currentPassword, lt];
    
    NSData *postData = [post dataUsingEncoding:NSASCIIStringEncoding allowLossyConversion:YES];
    
    NSString *postLength = [NSString stringWithFormat:@"%lu", ( unsigned long )[postData length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] init];
    
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
                                                         //Token Abfrage
                                                         NSURL *url = [NSURL URLWithString:@"https://moodle.herwegh.me/mod/tals/token.php"];
                                                         NSData *data = [NSData dataWithContentsOfURL:url];
                                                         NSError *error = nil;
                                                         BOOL result = NO;
                                                         //NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
                                                         NSDictionary *dataDictionary = [NSJSONSerialization
                                                                                         JSONObjectWithData:data options:0 error:&error];
                                                         TokenObject *currentToken =[[TokenObject alloc]initWithId:[[dataDictionary
                                                                                                                     objectForKey:@"id"]integerValue] Token:[dataDictionary objectForKey:@"token"]
                                                                                                              UserID:[dataDictionary objectForKey:@"userid"] ExternalService:[dataDictionary                                                                                                                                                                objectForKey:@"externalserviceid"] ValidUntil:[dataDictionary objectForKey:@"validuntil"] CheckLogged:result];
                                                         if (currentToken.token != nil) {
                                                             currentToken.checkLogged = YES;
                                                         }
                                                         NSLog(@"Token:%@", currentToken.token);
                                                         NSLog(@"User ID:%@", currentToken.userID);
                                                         NSLog(@"External Service:%@", currentToken.externalService);
                                                         NSLog(@"Valid Until:%@", currentToken.validTime);
                                                         NSLog(@"Check Logged:%i", currentToken.checkLogged);
                                                         if (error)
                                                             failure(error);
                                                         else {
                                                             success(currentToken);
                                                         }
                                                         //NSLog(@"%@",ret);
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

+(void)getToken:(NSString *)username password:(NSString *)password token:(void (^)(TokenObject *token))completionHandler{
    [self loginCAS:username Password:password success:^(TokenObject *tokenObj){
        if(completionHandler){
            completionHandler(tokenObj);
        }
    } failure:^(NSError *error){

    }];
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

+(void)clearAllCookies {
    NSHTTPCookieStorage *cookieStorage = [NSHTTPCookieStorage sharedHTTPCookieStorage];
    for (NSHTTPCookie *each in cookieStorage.cookies) {
        [cookieStorage deleteCookie:each];
    }
}

- (void)dealloc {
    // Should never be called, but just here for clarity really.
}

@end
