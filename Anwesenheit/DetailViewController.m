//
//  DetailViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "DetailViewController.h"

@interface DetailViewController ()

@end

@implementation DetailViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    // Do any additional setup after loading the view.
    self.navigationItem.title = self.detailModal[0];
    self.detailTitel.text = self.detailModal[0];
    self.detailDescription.text = self.detailModal[1];
    self.detailActive.text = self.detailModal[2];
    
    if([self.detailActive.text isEqualToString: @"Nächster Termin: Beispieldatum"]){
        self.detailPIN.hidden = YES;
        self.detailPinEntry.hidden = YES;
    }
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

/*
#pragma mark - Navigation

// In a storyboard-based application, you will often want to do a little preparation before navigation
- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    // Get the new view controller using [segue destinationViewController].
    // Pass the selected object to the new view controller.
}
*/

- (IBAction)enterPIN:(id)sender {
    [self.navigationController popToRootViewControllerAnimated:YES];
}
@end
