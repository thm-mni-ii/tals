//
//  AppData.m
//  Anwesenheit
//
//  Created by Sarah B on 05.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//
//  Contains all functions for communicating with Moodle and necessary helper functions.
//

#import "AppData.h"
#import "ClassObject.h"
#import "CourseObject.h"
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

// Get a CAS Login Ticket
// Does a simple NSURL request and extracts the LoginTicket
// returns (NSString *) LoginTicket
+ (NSString *)getLT {
  NSURL *url =
      [NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://"
                           @"fk-vv.mni.thm.de/moodle/login/index.php"];
  NSData *data = [NSData dataWithContentsOfURL:url];
  NSString *ret =
      [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
  NSString *search = @"LT-";
  NSArray *sub23 = [ret componentsSeparatedByString:search];
  NSArray *sub24 = [sub23[1] componentsSeparatedByString:@"\""];
  NSString *sub2 = sub24.firstObject;
  NSString *lt = [NSString stringWithFormat:@"LT-%@", sub2];
  return lt;
}

// Login to the appropriate service with CAS
// +Username Username
// +CurrentPassword Password
+ (void)loginCAS:(NSString *)username
        Password:(NSString *)currentPassword
         success:(void (^)(TokenObject *responseDict))success
         failure:(void (^)(NSError *error))failure {

  // clears the Cookies to make sure there are no leftovers from an older
  // session
  TokenObject *currentToken = nil;
  if (!currentToken) {
    [self clearAllCookies];
  }
  // extraction of Login Ticket
  NSString *lt = self.getLT;

  // LoginProcess
  // Sets up all the data required for the POST request
  NSCharacterSet *allowedCharacters =
      [NSCharacterSet alphanumericCharacterSet];
  NSString *pw2 = [currentPassword
      stringByAddingPercentEncodingWithAllowedCharacters:allowedCharacters];
  NSString *post = [NSString
      stringWithFormat:@"username=%@&password=%@&lt=%@&execution=e1s1&gateway="
                       @"true&_eventId=submit&submit=Anmelden",
                       username, pw2, lt];
  NSData *postData =
      [post dataUsingEncoding:NSASCIIStringEncoding allowLossyConversion:YES];

  NSString *postLength =
      [NSString stringWithFormat:@"%lu", (unsigned long)[postData length]];

  NSMutableURLRequest *request = [[NSMutableURLRequest alloc] init];

  [request setURL:[NSURL URLWithString:
                             @"https://cas.thm.de:443/cas/login?service=https:/"
                             @"/fk-vv.mni.thm.de/moodle/login/index.php"]];

  [request setHTTPMethod:@"POST"];
  [request setValue:postLength forHTTPHeaderField:@"Content-Length"];
  [request setValue:@"application/x-www-form-urlencoded"
      forHTTPHeaderField:@"Content-Type"];
  [request setHTTPBody:postData];

  // Starts asynchrounous Session to retrieve data stored in the JSON Format and
  // store it inside a Token Object
  NSURLSessionDataTask *task = [[self getURLSession]
      dataTaskWithRequest:request
        completionHandler:^(NSData *data, NSURLResponse *response,
                            NSError *error) {
          dispatch_async(dispatch_get_main_queue(), ^{
            // parse returned data
            // Token Abfrage
            NSURL *url = [NSURL
                URLWithString:
                    @"https://fk-vv.mni.thm.de/moodle/mod/tals/token.php"];
            NSData *data = [NSData dataWithContentsOfURL:url];
            NSError *error = nil;
            BOOL result = NO;
            NSDictionary *dataDictionary =
                [NSJSONSerialization JSONObjectWithData:data
                                                options:0
                                                  error:&error];
            TokenObject *currentToken = [[TokenObject alloc]
                     initWithId:[[dataDictionary objectForKey:@"id"]
                                    integerValue]
                          Token:[dataDictionary objectForKey:@"token"]
                         UserID:[dataDictionary objectForKey:@"userid"]
                ExternalService:[dataDictionary
                                    objectForKey:@"externalserviceid"]
                     ValidUntil:[dataDictionary objectForKey:@"validuntil"]
                    CheckLogged:result];
            if (currentToken.token != nil) {
              currentToken.checkLogged = YES;
            }
            NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
            [defaults setValue:currentToken.userID forKey:@"userID"];
            [defaults setValue:currentToken.token forKey:@"token"];
            [defaults setValue:currentToken.externalService
                        forKey:@"externalService"];
            [defaults setValue:currentToken.validTime forKey:@"validTime"];
            [defaults synchronize];
            if (error)
              failure(error);
            else {
              success(currentToken);
            }
          });
        }];

  [task resume];
}

// Get the User Token for the current service
// +Username Username
// +Password Password
+ (void)getToken:(NSString *)username
        password:(NSString *)password
           token:(void (^)(TokenObject *token))completionHandler
             err:(void (^)(NSError *err))errorHandler {
  [self loginCAS:username
        Password:password
         success:^(TokenObject *tokenObj) {
           if (completionHandler) {
             completionHandler(tokenObj);
           }
         }
         failure:^(NSError *error) {
             if (errorHandler) {
                 errorHandler(error);
             }
         }];
}

// Gets the Moodle data for a specified function
// +Function The function
// +AdValues Additional Values
// returns (NSData *) data
+ (NSData *)getData:(NSString *)function adValues:(NSString *) additionalValues {
  NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
  NSString *token = [defaults valueForKey:@"token"];
  NSString *userID = [defaults valueForKey:@"userID"];
  NSString *post = [NSString
      stringWithFormat:@"https://fk-vv.mni.thm.de/moodle/webservice/rest/"
                       @"server.php?wstoken=%@&wsfunction=%@&userid=%@&%@"
                       @"&moodlewsrestformat=json",
                       token, function, userID, additionalValues];
  NSURL *url = [NSURL URLWithString:post];
  NSData *data = [NSData dataWithContentsOfURL:url];
  return data;
}

// Check if a Token is valid
// Reads all the required data from the Userdefaults, then tries to request a
// page on the current Moodle. returns BOOL
+ (BOOL)checkToken {
  NSData *data = [self getData:@"mod_wstals_get_todays_appointments" adValues:@""];
  NSString *ret =
      [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
  if ([ret containsString:@"exception"]) {
    return NO;
  }
  return YES;
}

// Get all appointments for the day
// returns (NSArray *) appointmentArray containing all appointments found
+ (NSArray *)getAppointments {
  NSData *data = [self getData:@"mod_wstals_get_todays_appointments" adValues:@""];
  if (data == nil) {
    return nil;
  }
  NSError *error = nil;
  NSDictionary *dataDictionary =
      [NSJSONSerialization JSONObjectWithData:data options:0 error:&error];
  if (error) {
  }

  NSInteger length = [dataDictionary count];
  NSMutableArray *appointmentArray =
      [[NSMutableArray alloc] initWithCapacity:length];
  for (NSDictionary *appointment in dataDictionary) {
    int success = [[appointment valueForKey:@"pin"] intValue];
    ClassObject *currentAppointment = [[ClassObject alloc]
                initWithId:[[appointment objectForKey:@"id"] integerValue]
                     title:[appointment objectForKey:@"title"]
                 startdate:[appointment objectForKey:@"start"]
                   enddate:[appointment objectForKey:@"end"]
        currentdescription:[appointment objectForKey:@"description"]
                  courseid:[appointment objectForKey:@"courseid"]
                      type:[appointment objectForKey:@"type"]
                       pin:success];
    [appointmentArray addObject:currentAppointment];
  }
  return appointmentArray;
}

// Get all courses for the current User
// returns (NSArray *) coursesArray with all courses for the currently logged in User
+ (NSArray *)getCourses {
  NSData *data = [self getData:@"mod_wstals_get_courses" adValues:@""];
  if (data == nil) {
    return nil;
  }
  NSError *error = nil;
  NSDictionary *dataDictionary =
      [NSJSONSerialization JSONObjectWithData:data options:0 error:&error];
  if (error) {
  }

  NSInteger length = [dataDictionary count];
  NSMutableArray *coursesArray =
      [[NSMutableArray alloc] initWithCapacity:length];
  for (NSDictionary *course in dataDictionary) {
    CourseObject *currentCourse = [[CourseObject alloc]
        initWithId:[[course objectForKey:@"id"] integerValue]
         shortname:[course objectForKey:@"shortname"]
          fullname:[course objectForKey:@"fullname"]
         startdate:[course objectForKey:@"startdate"]];
    [coursesArray addObject:currentCourse];
  }
  return coursesArray;
}

// Send PIN to server and handle reply
// +AppointmentID The appointments ID.
// +Pin The PIN
// returns BOOL
+ (BOOL)sendPIN:(NSString *)appointmentID pin:(NSString *)pin {
    NSString *values = [NSString
                      stringWithFormat: @"appointmentid=%@&pinum=%@",
                      appointmentID, pin];
    NSData *data = [self getData:@"mod_wstals_insert_attendance" adValues:values];
    NSString *ret =
        [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    if ([ret containsString:@"Erfolgreich"]) {
        return YES;
    }
    return NO;
}

// Get days absent for a specified appointment, server method not fully implemented
// +AppointmentID The appointments ID.
// returns (NSString *) ret Number of days absent so far
+ (NSString *)getDaysAbsent:(id)appointmentID {
  NSString *aiD = [NSString stringWithFormat:@"%@", appointmentID];
  NSString *values = [NSString
                        stringWithFormat: @"appointmentid=%@",
                        aiD];
  NSData *data = [self getData:@"mod_wstals_check_for_enabled_pin" adValues:values];
  NSError *error = nil;
  NSDictionary *dataDictionary =
      [NSJSONSerialization JSONObjectWithData:data options:0 error:&error];
  if (error) {
  }
  NSString *ret = [dataDictionary objectForKey:@"days absent"];
  return ret;
}

// Checks if the connection is working
// returns BOOL
+ (BOOL)checkConnection {
  NSURL *scriptUrl =
      [NSURL URLWithString:@"https://cas.thm.de:443/cas/login?service=https://"
                           @"fk-vv.mni.thm.de/moodle/login/index.php"];
  NSData *data = [NSData dataWithContentsOfURL:scriptUrl];
  NSString *ret =
      [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
  if (![ret containsString:@"HTTP Status"])
    return YES;
  else
    return NO;
}

// Gets a URL Session
// returns (NSURLSession *) session
+ (NSURLSession *)getURLSession {
  static NSURLSession *session = nil;
  static dispatch_once_t onceToken;
  dispatch_once(&onceToken, ^{
    NSURLSessionConfiguration *configuration =
        [NSURLSessionConfiguration defaultSessionConfiguration];
    session = [NSURLSession sessionWithConfiguration:configuration];
  });

  return session;
}

// Removes all cookies currently in the CookieStorage
+ (void)clearAllCookies {
  NSHTTPCookieStorage *cookieStorage =
      [NSHTTPCookieStorage sharedHTTPCookieStorage];
  for (NSHTTPCookie *each in cookieStorage.cookies) {
    [cookieStorage deleteCookie:each];
  }
}

@end
