//
//  TokenObject.m
//  Anwesenheit
//
//  Created by Sarah B on 14.12.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "TokenObject.h"

@implementation TokenObject
-(instancetype)initWithId:(int)Id Token:(NSString *)currentToken UserID:(NSString
                                                                         *)givenUserID ExternalService:(NSString *)givenExternalService ValidUntil:(NSString *)givenValidTime CheckLogged:(BOOL)givenLogged{
    self = [super init];
    if(self){
        self.Id = Id;
        self.token = currentToken;
        self.userID = givenUserID;
        self.externalService = givenExternalService;
        self.validTime = givenValidTime;
        self.checkLogged = givenLogged;
    }
    return self;
}

@end
