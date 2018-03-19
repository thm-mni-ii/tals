//
//  ClassObject.m
//  Anwesenheit
//
//  Created by Sarah B on 17.01.18.
//  Copyright © 2018 THM Gießen. All rights reserved.
//
//
// Object containing a class with the proper values, to be used for entering
// your attendance
//

#import "ClassObject.h"

@implementation ClassObject
- (instancetype)initWithId:(int)Id
                     title:(NSString *)currentTitle
                 startdate:(NSString *)givenStartdate
                   enddate:(NSString *)givenEnddate
        currentdescription:(NSString *)currentDescription
                  courseid:(NSString *)currentCourseid
                      type:(NSString *)currentType
                       pin:(int)currentActive;
{
  self = [super init];
  if (self) {
    self.Id = Id;
    self.title = currentTitle;
    self.startdate = givenStartdate;
    self.enddate = givenEnddate;
    self.currentdescription = currentDescription;
    self.courseid = currentCourseid;
    self.type = currentType;
    self.pin = currentActive;
  }
  return self;
}

@end
