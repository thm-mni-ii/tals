#import "AppData.h"
#import "TokenObject.h"
#import "ClassObject.h"

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
    NSArray *sub23 = [ret componentsSeparatedByString:search];
    NSArray *sub24 = [sub23[1] componentsSeparatedByString:@"\""];
    NSString *sub2 = sub24.firstObject;
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
                                                         NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
                                                         [defaults setValue:currentToken.userID forKey:@"userID"];
                                                         [defaults setValue:currentToken.token forKey:@"token"];
                                                         [defaults setValue:currentToken.externalService forKey:@"externalService"];
                                                         [defaults setValue:currentToken.validTime forKey:@"validTime"];
                                                         [defaults synchronize];
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
                                                         }*/
                                                         /*NSString *resulte = [[NSString alloc] initWithData:data encoding:NSASCIIStringEncoding];
                                                         NSLog( @"%@", resulte );
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

+(BOOL) checkToken{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://moodle.herwegh.me/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_get_todays_appointments&userid=%@&moodlewsrestformat=json", token, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    if([ret containsString:@"exception"]){
        return NO;
    }
    return YES;
}

+(NSArray * )getClasses{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://moodle.herwegh.me/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_get_todays_appointments&userid=%@&moodlewsrestformat=json", token, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    if(data == nil){
        return nil;
    }
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSLog(ret);
    NSError *error = nil;
    NSDictionary *dataDictionary = [NSJSONSerialization
                                    JSONObjectWithData:data options:0 error:&error];
    if(error){
        NSLog(@"Error!");
    }
    
    NSInteger length = [dataDictionary count];
    NSMutableArray *classesArray = [[NSMutableArray alloc] initWithCapacity:length];
    for (NSDictionary *class in dataDictionary)
    {
        int success = [[class valueForKey:@"pin"] intValue];
        ClassObject *currentClass =[[ClassObject alloc]initWithId:[[class
                                                                    objectForKey:@"id"]integerValue] title:[class objectForKey:@"title"]
                                                        startdate:[class objectForKey:@"start"] enddate:[class                                                                                                                                                                objectForKey:@"end"] currentdescription:[class objectForKey:@"description"] courseid:[class objectForKey:@"courseid"] type:[class objectForKey:@"type"] pin:success];
        [classesArray addObject:currentClass];
    }
    return classesArray;
}

+ (BOOL) getPinActive:(int) appointmentID{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://moodle.herwegh.me/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_check_for_enabled_pin&appointmentid=%i&userid=%@&moodlewsrestformat=json", token, appointmentID, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSLog(post);
    NSLog(ret);
    NSLog(@"%i",appointmentID);
    if([ret containsString:@"true"]){
        return YES;
    }
    return NO;
}
+ (BOOL) sendPIN:(NSString *) appointmentID pin:(NSString *) pin{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://moodle.herwegh.me/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_insert_attendance&appointmentid=%@&userid=%@&pinum=%@&moodlewsrestformat=json", token, appointmentID, userID, pin];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSLog(post);
    NSLog(ret);
    if([ret containsString:@"Success"]){
        return YES;}
    return NO;
}

+ (int) getDaysAbsent:(int) appointmentID{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://moodle.herwegh.me/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_get_days_absent&appointmentid=%i&userid=%@&moodlewsrestformat=json", token, appointmentID, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSLog(post);
    NSLog(ret);
    if([ret containsString:@"success"]){
        return 1;}
    return 0;
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
