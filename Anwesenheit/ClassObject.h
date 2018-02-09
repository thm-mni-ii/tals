//
//  ClassObject.h
//  Anwesenheit
//
//  Created by Sarah B on 17.01.18.
//  Copyright © 2018 THM Gießen. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface ClassObject : NSObject
-(instancetype)initWithId:(int)Id title:(NSString *)currentTitle startdate:(NSString *)givenStartdate enddate:(NSString *)givenEnddate currentdescription:(NSString *)currentDescription courseid:(NSString *)currentCourseid type:(NSString *) currentType pin:(int) currentActive;

@property (nonatomic) int Id;
@property (nonatomic,strong) NSString * title;
@property (nonatomic,strong) NSString * startdate;
@property (nonatomic,strong) NSString * enddate;
@property (nonatomic,strong) NSString * currentdescription;
@property (nonatomic,strong) NSString * courseid;
@property (nonatomic,strong) NSString * type;
@property (nonatomic) int pin;
@end
