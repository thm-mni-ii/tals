//
//  ClassObject.h
//  Anwesenheit
//
//  Created by Sarah B on 17.01.18.
//  Copyright © 2018 THM Gießen. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface ClassObject : NSObject
-(instancetype)initWithId:(int)Id Shortname:(NSString *)currentShortname Fullname:(NSString
                                                                         *)currentFullname Startdate:(long)givenStartdate;
@property (nonatomic) int Id;
@property (nonatomic,strong) NSString * shortname;
@property (nonatomic,strong) NSString * fullname;
@property (nonatomic) long startdate;
@end
