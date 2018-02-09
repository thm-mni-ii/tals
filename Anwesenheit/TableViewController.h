//
//  TableViewController.h
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "TableViewCell.h"

@interface TableViewController : UITableViewController {
    
    NSArray *classes;
    NSMutableArray *title;
    NSMutableArray *description;
    NSMutableArray *active;
    NSMutableArray *courseid;
    NSMutableArray *start;
    NSMutableArray *end;
    BOOL data;

    
}

@end
