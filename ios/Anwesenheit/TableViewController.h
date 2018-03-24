//
//  TableViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//
//  Responsible for the TableView that holds the currently active classes info
//

#import "TableViewCell.h"
#import <UIKit/UIKit.h>

@interface TableViewController : UITableViewController {

  NSArray *classes;
  NSArray *courses;
  NSMutableArray *course;
  NSMutableArray *title;
  NSMutableArray *description;
  NSMutableArray *type;
  NSMutableArray *active;
  NSMutableArray *courseid;
  NSMutableArray *start;
  NSMutableArray *end;
  BOOL data;
}

@end
