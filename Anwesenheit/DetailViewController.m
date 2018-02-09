//
//  DetailViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "DetailViewController.h"
#import "AppData.h"

@interface DetailViewController ()

@end

@implementation DetailViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    // Do any additional setup after loading the view.
    self.navigationItem.title = self.detailModal[0];
    self.detailTitel.text = self.detailModal[0];
    self.detailDescription.text = self.detailModal[1];
    NSString * active = self.detailModal[2];
    NSLog(@"Aktiv: %@", self.detailModal[2]);
    if([active isEqualToString:@"1"]){
        self.detailActive.text = @"Momentan aktiv";}
    else{
        self.detailActive.text = [NSString stringWithFormat:@"Kurs aktiv von %@ Uhr bis %@ Uhr.", self.detailModal[4], self.detailModal[5]];
        self.detailPIN.hidden = YES;
        self.detailPinEntry.hidden = YES;
        self.detailSend.hidden = YES;
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


- (IBAction)sendPin:(id)sender {
    if([AppData sendPIN:self.detailModal[3] pin:self.detailPinEntry.text]){
        self.detailActive.text = @"Erfolgreich eingetragen.";
        self.detailPIN.hidden = YES;
        self.detailPinEntry.hidden = YES;
        self.detailSend.hidden = YES;
        [self.view endEditing:YES];
        UIAlertController *alertController = [UIAlertController  alertControllerWithTitle:@"Erfolg"  message:@"Sie sind erfolgreich in den Kurs eingetragen."  preferredStyle:UIAlertControllerStyleAlert];
        [alertController addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
            [self dismissViewControllerAnimated:YES completion:nil];
            
        }]];
        [self presentViewController:alertController animated:YES completion:nil];
        }
    else{
        UIAlertController *alertController = [UIAlertController  alertControllerWithTitle:@"Fehler"  message:@"Die PIN ist falsch oder der Termin abeglaufen."  preferredStyle:UIAlertControllerStyleAlert];
        [alertController addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
            [self dismissViewControllerAnimated:YES completion:nil];
        }]];
        [self presentViewController:alertController animated:YES completion:nil];
    }
}
@end
