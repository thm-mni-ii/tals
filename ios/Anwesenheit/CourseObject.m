//
//  CourseObject.m
//  Anwesenheit
//
//  Created by Sarah B on 11.02.18.
//  Copyright © 2018 THM Gießen. All rights reserved.
//
//
// Object containing a course with the proper values, to be used for showing
// information about said course
//

#import "CourseObject.h"

@implementation CourseObject
- (instancetype)initWithId:(int)Id
                 shortname:(NSString *)currentShortname
                  fullname:(NSString *)givenFullname
                 startdate:(NSString *)givenStartdate;
{
  self = [super init];
  if (self) {
    self.Id = Id;
    self.shortname = currentShortname;
    self.startdate = givenStartdate;
    self.fullname = givenFullname;
  }
  return self;
}

@end
