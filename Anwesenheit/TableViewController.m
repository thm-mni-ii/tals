//
//  TableViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "TableViewController.h"
#import "DetailViewController.h"
#import "TokenObject.h"
#import "AppDelegate.h"
#import "AppData.h"
#import "ClassObject.h"

@interface TableViewController ()

@end

@implementation TableViewController

- (void)viewDidLoad {
    [super viewDidLoad];
    UIRefreshControl *refreshControl = [[UIRefreshControl alloc] init];
    [refreshControl addTarget:self action:@selector(refresh:) forControlEvents:UIControlEventValueChanged];
    [self.tableView addSubview:refreshControl];
    UIBarButtonItem *logoutButton = [[UIBarButtonItem alloc]
                                   initWithTitle:@"Logout"
                                   style:UIBarButtonItemStylePlain
                                   target:self
                                   action:@selector((buttonTapped:))];
    self.navigationItem.rightBarButtonItem = logoutButton;
    
    title = [NSMutableArray array];
    description = [NSMutableArray array];
    active = [NSMutableArray array];
    courseid = [NSMutableArray array];
    start = [NSMutableArray array];
    end = [NSMutableArray array];
    
    if([AppData checkToken]){
        classes = [AppData getClasses];
        long classesA = [classes count];
        NSLog(@"%lui",classesA);
        if(classesA != 0){
            data = YES;
            [self updateClasses:classes];
            NSLog(@"%@", description[0]);
            NSLog(@"%lui",[description count]);
        }
        else {
            data = NO;
        }
    }
    [[self navigationItem] setBackBarButtonItem:[[UIBarButtonItem alloc] initWithTitle:@"" style:UIBarButtonItemStylePlain target:nil action:nil]];

}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}
- (void) buttonTapped: (UIButton*) sender
{
    NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
    [defaults setValue:0 forKey:@"userID"];
    [defaults setValue:0 forKey:@"token"];
    [defaults setValue:0 forKey:@"externalService"];
    [defaults setValue:0 forKey:@"validTime"];
    [defaults synchronize];
    UIStoryboard *storyboard = [UIStoryboard storyboardWithName:@"Main" bundle:nil];
    UIViewController *LoginViewController = [storyboard instantiateViewControllerWithIdentifier:@"loginScreen"];
    [self presentViewController:LoginViewController animated:YES completion:nil];
}
#pragma mark - Table view data source

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    UILabel *messageLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, self.view.bounds.size.width, self.view.bounds.size.height)];
    
    messageLabel.text = @"No classes found. Please pull down to refresh.";
    messageLabel.numberOfLines = 0;
    messageLabel.textAlignment = NSTextAlignmentCenter;
    messageLabel.font = [UIFont fontWithName:@"AvenirNext-Regular" size:20];
    [messageLabel sizeToFit];
    
    if (data) {
        messageLabel.textColor = [UIColor whiteColor];
        self.tableView.backgroundView = messageLabel;
        self.tableView.separatorStyle = UITableViewCellSeparatorStyleSingleLine;
        return 1;
        
    } else {
        
        // Display a message when the table is empty
        
        messageLabel.textColor = [UIColor blackColor];
        self.tableView.backgroundView = messageLabel;
        self.tableView.separatorStyle = UITableViewCellSeparatorStyleNone;
        
    }
    
    return 0;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    return classes.count;
}


- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    
    
    TableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:@"Cell" forIndexPath:indexPath];
    /*ClassObject *currentCell = classes[indexPath.row];
    cell.cellTitel.text = currentCell.fullname;
    BOOL active = [AppData getPinActive:currentCell.Id];
    if(!active){
        cell.cellDescription.text = @"Aktiv";
    }
    else{
        NSDate *retrievedDateItem = [[NSDate alloc] initWithTimeIntervalSince1970:currentCell.startdate];
        NSDateFormatter *dateFormatter = [[NSDateFormatter alloc] init];
        [dateFormatter setDateStyle:NSDateFormatterMediumStyle];
        [dateFormatter setTimeStyle:NSDateFormatterMediumStyle];
        NSString *convertedDate = [dateFormatter stringFromDate:retrievedDateItem];
        NSString * result = [NSString stringWithFormat:@"Aktiv ab:%@", convertedDate];
        cell.cellDescription.text = result;
    }*/
    cell.cellTitle.text = title[indexPath.row];
    cell.cellDescription.text = description[indexPath.row];
    
    // Configure the cell...
    
    return cell;
}

- (void)refresh:(UIRefreshControl *)refreshControl {
    // Do your job, when done:
    classes = [AppData getClasses];
    [title removeAllObjects];
    [description removeAllObjects];
    [active removeAllObjects];
    [courseid removeAllObjects];
    [start removeAllObjects];
    [end removeAllObjects];
    long classesA = [classes count];
    if(classesA != 0){
        data = YES;
        [self updateClasses:classes];
    }
    else {
        data = NO;
    }
    [self.tableView reloadData];
    [refreshControl endRefreshing];
}

- (void)updateClasses:(NSArray *)classes{
    for (ClassObject * class in classes) {
        NSLog(@"%@", class.currentdescription);
        NSLog(@"%@", class.title);
        NSLog(@"%d", class.pin);
        NSString * aiD = [NSString stringWithFormat:@"%d",class.Id];
        NSString *pin= [NSString stringWithFormat:@"%d",class.pin];
        NSString *starte = class.startdate;
        NSString *ende = class.enddate;
        [title addObject:class.title];
        [description addObject:class.currentdescription];
        [active addObject:pin];
        [courseid addObject:aiD];
        [start addObject:starte];
        [end addObject:ende];
        NSLog(@"Kurs: %@",courseid[0]);
        NSLog(@"Start: %@",start[0]);
        NSLog(@"End: %@",end[0]);
    }
}

/*
// Override to support conditional editing of the table view.
- (BOOL)tableView:(UITableView *)tableView canEditRowAtIndexPath:(NSIndexPath *)indexPath {
    // Return NO if you do not want the specified item to be editable.
    return YES;
}
*/

/*
// Override to support editing the table view.
- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath:(NSIndexPath *)indexPath {
    if (editingStyle == UITableViewCellEditingStyleDelete) {
        // Delete the row from the data source
        [tableView deleteRowsAtIndexPaths:@[indexPath] withRowAnimation:UITableViewRowAnimationFade];
    } else if (editingStyle == UITableViewCellEditingStyleInsert) {
        // Create a new instance of the appropriate class, insert it into the array, and add a new row to the table view
    }   
}
*/

/*
// Override to support rearranging the table view.
- (void)tableView:(UITableView *)tableView moveRowAtIndexPath:(NSIndexPath *)fromIndexPath toIndexPath:(NSIndexPath *)toIndexPath {
}
*/

/*
// Override to support conditional rearranging of the table view.
- (BOOL)tableView:(UITableView *)tableView canMoveRowAtIndexPath:(NSIndexPath *)indexPath {
    // Return NO if you do not want the item to be re-orderable.
    return YES;
}
*/



#pragma mark - Navigation

// In a storyboard-based application, you will often want to do a little preparation before navigation
- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if([[segue identifier] isEqualToString:@"showDetail"]){
        DetailViewController *detailView = [segue destinationViewController];
        
        NSIndexPath *myIndexPath = [self.tableView indexPathForSelectedRow];
        
        int row = (int)[myIndexPath row];
        detailView.detailModal = @[title[row], description[row], active[row], courseid[row], start[row], end[row]];
    }
}


@end
