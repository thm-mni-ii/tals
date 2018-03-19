//
//  TokenObject.h
//  Anwesenheit
//
//  Created by Sarah B on 14.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//
//
// Object containing a Token used request data from the server
// +Id Token ID
// +Token The user token
// +UserID The user ID
// +ExternalService The requested service
// +ValidTime Time the token is valid for
// +CheckLogged BOOL if token is valid or not
//

#import <Foundation/Foundation.h>

@interface TokenObject : NSObject
- (instancetype)initWithId:(int)Id
                     Token:(NSString *)currentToken
                    UserID:(NSString *)givenUserID
           ExternalService:(NSString *)givenExternalService
                ValidUntil:(NSString *)givenValidTime
               CheckLogged:(BOOL)checkLogged;
@property(nonatomic) int Id;
@property(nonatomic, strong) NSString *token;
@property(nonatomic, strong) NSString *userID;
@property(nonatomic, strong) NSString *externalService;
@property(nonatomic, strong) NSString *validTime;
@property(nonatomic, assign) BOOL checkLogged;
@end
