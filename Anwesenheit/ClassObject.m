//
//  ClassObject.m
//  Anwesenheit
//
//  Created by Sarah B on 17.01.18.
//  Copyright © 2018 THM Gießen. All rights reserved.
//

#import "ClassObject.h"

@implementation ClassObject
-(instancetype)initWithId:(int)Id Shortname:(NSString *)currentShortname Fullname:(NSString
                                                                                   *)currentFullname Startdate:(long)givenStartdate{
    self = [super init];
    if(self){
        self.Id = Id;
        self.shortname = currentShortname;
        self.fullname = currentFullname;
        self.startdate = *(&(givenStartdate));
    }
    return self;
}

@end

