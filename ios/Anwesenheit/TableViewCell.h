//
//  TableViewCell.h
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface TableViewCell : UITableViewCell
@property(weak, nonatomic) IBOutlet UILabel *cellTitle;
@property(weak, nonatomic) IBOutlet UILabel *cellDescription;
@property(weak, nonatomic) IBOutlet UILabel *cellTimes;
@property(weak, nonatomic) IBOutlet UIImageView *cellLocked;

@end
