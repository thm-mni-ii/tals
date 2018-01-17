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
    NSArray *title;
    NSArray *description;
    NSArray *active;
    BOOL data;

    
}

@end
