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
    // Setup the detailed view with the data received from the TableView
    self.navigationItem.title = self.detailModal[6];
    self.detailTitel.text = self.detailModal[0];
    self.detailDescription.text = self.detailModal[1];
    self.detailType.text = [NSString stringWithFormat:@"Typ: %@", self.detailModal[7]];
    NSString * active = self.detailModal[2];
    if([AppData sendPIN:self.detailModal[3] pin:@"123456"]){
        self.detailActive.text = @"Erfolgreich eingetragen.";
        self.detailPIN.hidden = YES;
        self.detailPinEntry.hidden = YES;
        self.detailSend.hidden = YES;
    }
    else{
        if([active isEqualToString:@"1"]){
            self.detailActive.text = @"Status: Momentan aktiv";}
        else{
            self.detailActive.text = [NSString stringWithFormat:@"Kurs aktiv von %@ Uhr bis %@ Uhr.", self.detailModal[4], self.detailModal[5]];
            self.detailPIN.hidden = YES;
            self.detailPinEntry.hidden = YES;
            self.detailSend.hidden = YES;
        }
    }
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


// Send PIN and display appropriate Alerts depending on the result
- (void) pinHandover{
    if([AppData sendPIN:self.detailModal[3] pin:self.detailPinEntry.text]){
        self.detailActive.text = @"Erfolgreich eingetragen.";
        self.detailPIN.hidden = YES;
        self.detailPinEntry.hidden = YES;
        self.detailSend.hidden = YES;
        [self.view endEditing:YES];
        UIAlertController *alertController2 = [UIAlertController  alertControllerWithTitle:@"Erfolg"  message:@"Sie sind erfolgreich in den Kurs eingetragen."   preferredStyle:UIAlertControllerStyleAlert];
        [alertController2 addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
        }]];
        UIAlertController *alertController = [UIAlertController  alertControllerWithTitle:@"Fehltage"  message:[NSString stringWithFormat:@"Sie haben: %@ Fehltag(e)", [NSString stringWithFormat:@"%@", [AppData getDaysAbsent:self.detailModal[3]]]] preferredStyle:UIAlertControllerStyleAlert];
        [alertController addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
             [self presentViewController:alertController2 animated:YES completion:nil];
        }]];
        [self presentViewController:alertController animated:YES completion:nil];
    }
    else{
        UIAlertController *alertController = [UIAlertController  alertControllerWithTitle:@"Fehler"  message:@"Die PIN ist falsch oder der Termin abeglaufen."  preferredStyle:UIAlertControllerStyleAlert];
        [alertController addAction:[UIAlertAction actionWithTitle:@"OK" style:UIAlertActionStyleDefault handler:^(UIAlertAction *action) {
        }]];
        [self presentViewController:alertController animated:YES completion:nil];
    }
    
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField
{
    [textField resignFirstResponder]; // Dismiss the keyboard.
    [self pinHandover];
    return YES;
}


- (IBAction)sendPin:(id)sender {
    [self pinHandover];
}
@end
