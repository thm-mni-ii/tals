//
//  DetailViewController.h
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface DetailViewController : UIViewController

@property(strong, nonatomic) NSArray *detailModal;
@property (weak, nonatomic) IBOutlet UITextField *detailPinEntry;
@property (weak, nonatomic) IBOutlet UILabel *detailTitel;
@property (weak, nonatomic) IBOutlet UIButton *detailSend;
@property (weak, nonatomic) IBOutlet UILabel *detailPIN;
@property (weak, nonatomic) IBOutlet UILabel *detailActive;
@property (weak, nonatomic) IBOutlet UILabel *detailDescription;
@property (weak, nonatomic) IBOutlet UILabel *detailType;
- (IBAction)sendPin:(id)sender;

@end
