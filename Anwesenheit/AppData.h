#import <foundation/Foundation.h>

@interface AppData : NSObject {
    BOOL *sLogged;
}

@property (nonatomic, assign) BOOL *sLogged;

+ (AppData *)SharedAppData;
+ (void) loginCAS;
+ (NSString *)getLT;
+ ( NSURLSession * )getURLSession;
+ (void)URLSession:(NSURLSession *)session
              task:(NSURLSessionTask *)task
willPerformHTTPRedirection:(NSHTTPURLResponse *)redirectResponse
        newRequest:(NSURLRequest *)request
 completionHandler:(void (^)(NSURLRequest *))completionHandler;

@end
