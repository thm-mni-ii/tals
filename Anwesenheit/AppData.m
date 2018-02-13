#import "AppData.h"
#import "TokenObject.h"
#import "ClassObject.h"
#import "CourseObject.h"

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

// Get a CAS Login Ticket
+ (NSString *)getLT{
    NSURL *url = [NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://fk-vv.mni.thm.de/moodle/login/index.php"];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    NSString *search = @"LT-";
    NSArray *sub23 = [ret componentsSeparatedByString:search];
    NSArray *sub24 = [sub23[1] componentsSeparatedByString:@"\""];
    NSString *sub2 = sub24.firstObject;
    NSString *lt = [NSString stringWithFormat:@"LT-%@", sub2];
    return lt;
}


// Login to the appropriate service with CAS
+ (void) loginCAS:(NSString *)username Password:(NSString *)currentPassword success:(void (^)(TokenObject *responseDict))success failure:(void(^)(NSError* error))failure {
    
    TokenObject *currentToken = nil;
    if (!currentToken) {
        [self clearAllCookies];
    }
    //extraction of Login Ticket
    NSString *lt = self.getLT;
    
    //LoginProcess
    NSCharacterSet *allowedCharacters = [NSCharacterSet URLFragmentAllowedCharacterSet];
    NSString *pw2 = [currentPassword stringByAddingPercentEncodingWithAllowedCharacters:allowedCharacters];
    NSString *post = [NSString stringWithFormat:@"username=%@&password=%@&lt=%@&execution=e1s1&gateway=true&_eventId=submit&submit=Anmelden", username, pw2, lt];
    NSData *postData = [post dataUsingEncoding:NSASCIIStringEncoding allowLossyConversion:YES];
    
    NSString *postLength = [NSString stringWithFormat:@"%lu", ( unsigned long )[postData length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] init];
    
    [request setURL:[NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://fk-vv.mni.thm.de/moodle/login/index.php"]];
    
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
                                                         NSURL *url = [NSURL URLWithString:@"https://fk-vv.mni.thm.de/moodle/mod/tals/token.php"];
                                                         NSData *data = [NSData dataWithContentsOfURL:url];
                                                         NSError *error = nil;
                                                         BOOL result = NO;
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
                                                         if (error)
                                                             failure(error);
                                                         else {
                                                             success(currentToken);
                                                         }
                                                     } );
                                  }];
    
    
    [task resume];
}

// Get the User Token for the current service
+(void)getToken:(NSString *)username password:(NSString *)password token:(void (^)(TokenObject *token))completionHandler{
    [self loginCAS:username Password:password success:^(TokenObject *tokenObj){
        if(completionHandler){
            completionHandler(tokenObj);
        }
    } failure:^(NSError *error){
        
    }];
}

// Check if a Token is valid
+(BOOL) checkToken{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://fk-vv.mni.thm.de/moodle/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_get_todays_appointments&userid=%@&moodlewsrestformat=json", token, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    if([ret containsString:@"exception"]){
        return NO;
    }
    return YES;
}

// Get all currently active appointments
+(NSArray * )getAppointments{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://fk-vv.mni.thm.de/moodle/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_get_todays_appointments&userid=%@&moodlewsrestformat=json", token, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    if(data == nil){
        return nil;
    }
    NSError *error = nil;
    NSDictionary *dataDictionary = [NSJSONSerialization
                                    JSONObjectWithData:data options:0 error:&error];
    if(error){
    }
    
    NSInteger length = [dataDictionary count];
    NSMutableArray *appointmentArray = [[NSMutableArray alloc] initWithCapacity:length];
    for (NSDictionary *appointment in dataDictionary)
    {
        int success = [[appointment valueForKey:@"pin"] intValue];
        ClassObject *currentAppointment =[[ClassObject alloc]initWithId:[[appointment
                                                                          objectForKey:@"id"]integerValue] title:[appointment objectForKey:@"title"]
                                                              startdate:[appointment objectForKey:@"start"] enddate:[appointment                                                                                                                                                                objectForKey:@"end"] currentdescription:[appointment objectForKey:@"description"] courseid:[appointment objectForKey:@"courseid"] type:[appointment objectForKey:@"type"] pin:success];
        [appointmentArray addObject:currentAppointment];
    }
    return appointmentArray;
}

// Get all courses for the current User
+(NSArray *)getCourses{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://fk-vv.mni.thm.de/moodle/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_get_courses&userid=%@&moodlewsrestformat=json", token, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    if(data == nil){
        return nil;
    }
    NSError *error = nil;
    NSDictionary *dataDictionary = [NSJSONSerialization
                                    JSONObjectWithData:data options:0 error:&error];
    if(error){
    }
    
    NSInteger length = [dataDictionary count];
    NSMutableArray *coursesArray = [[NSMutableArray alloc] initWithCapacity:length];
    for (NSDictionary *course in dataDictionary)
    {
        CourseObject *currentCourse =[[CourseObject alloc]initWithId:[[course
                                                                       objectForKey:@"id"]integerValue] shortname:[course objectForKey:@"shortname"] fullname:[course objectForKey:@"fullname"]
                                                           startdate:[course objectForKey:@"startdate"]];
        [coursesArray addObject:currentCourse];
    }
    return coursesArray;
}

// Send PIN to server and handle reply
+ (BOOL) sendPIN:(NSString *) appointmentID pin:(NSString *) pin{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://fk-vv.mni.thm.de/moodle/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_insert_attendance&appointmentid=%@&userid=%@&pinum=%@&moodlewsrestformat=json", token, appointmentID, userID, pin];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    if([ret containsString:@"Erfolgreich"]){
        return YES;}
    return NO;
}

// Get days absent for a specified appointment, not fully implemented
+ (NSString *) getDaysAbsent:(id) appointmentID{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    NSString * aiD = [NSString stringWithFormat:@"%@",appointmentID];
    NSString *token = [defaults valueForKey:@"token"];
    NSString *userID = [defaults valueForKey:@"userID"];
    NSString *post = [NSString stringWithFormat:@"https://fk-vv.mni.thm.de/moodle/webservice/rest/server.php?wstoken=%@&wsfunction=mod_wstals_check_for_enabled_pin&appointmentid=%@&userid=%@&moodlewsrestformat=json", token, aiD, userID];
    NSURL *url = [NSURL URLWithString:post];
    NSData *data = [NSData dataWithContentsOfURL:url];
    NSError *error = nil;
    NSDictionary *dataDictionary = [NSJSONSerialization
                                    JSONObjectWithData:data options:0 error:&error];
    if(error){
    }
    NSString *ret = [dataDictionary objectForKey:@"days absent"];
    return ret;
}

+ (BOOL) checkConnection{
    NSURL *scriptUrl = [NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://fk-vv.mni.thm.de/moodle/login/index.php"];
    NSData *data = [NSData dataWithContentsOfURL:scriptUrl];
    NSString *ret = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    if (![ret containsString:@"HTTP Status"])
        return YES;
    else
        return NO;
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

