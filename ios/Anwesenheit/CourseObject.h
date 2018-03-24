//
//  CourseObject.h
//  Anwesenheit
//
//  Created by Sarah B on 11.02.18.
//  Copyright © 2018 THM Gießen. All rights reserved.
//
//
// Object containing a course with the proper values, to be used for showing
// information about said course
// +Id Course ID
// +Shortname Short name for the course
// +Fullname Fullname for the course
// +Startdate Startdate of the course
//

#import <Foundation/Foundation.h>

@interface CourseObject : NSObject
- (instancetype)initWithId:(int)Id
                 shortname:(NSString *)currentShortname
                  fullname:(NSString *)givenFullname
                 startdate:(NSString *)givenStartdate;

@property(nonatomic) int Id;
@property(nonatomic, strong) NSString *shortname;
@property(nonatomic, strong) NSString *fullname;
@property(nonatomic, strong) NSString *startdate;
@end
