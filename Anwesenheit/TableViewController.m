//
//  TableViewController.m
//  Anwesenheit
//
//  Created by Sarah B on 23.11.17.
//  Copyright © 2017 THM Gießen. All rights reserved.
//

#import "TableViewController.h"
#import "AppData.h"
#import "AppDelegate.h"
#import "ClassObject.h"
#import "CourseObject.h"
#import "DetailViewController.h"
#import "TokenObject.h"

@interface TableViewController ()

@end

@implementation TableViewController

- (void)viewDidLoad {
  [super viewDidLoad];
  NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
  BOOL stayLogged = [defaults boolForKey:@"checkLogged"];
  if (!stayLogged) {
    [defaults setValue:0 forKey:@"userID"];
    [defaults setValue:0 forKey:@"token"];
    [defaults setValue:0 forKey:@"externalService"];
    [defaults setValue:0 forKey:@"validTime"];
    [defaults synchronize];
  }
  BOOL logged = [AppData checkToken];
  if (!logged) {
    [self showLoginScreen:NO];
  }
  UIRefreshControl *refreshControl = [[UIRefreshControl alloc] init];
  [refreshControl addTarget:self
                     action:@selector(refresh:)
           forControlEvents:UIControlEventValueChanged];
  [self.tableView addSubview:refreshControl];

  // Creates Logout Button
  UIBarButtonItem *logoutButton =
      [[UIBarButtonItem alloc] initWithTitle:@"Logout"
                                       style:UIBarButtonItemStylePlain
                                      target:self
                                      action:@selector((buttonTapped:))];
  self.navigationItem.rightBarButtonItem = logoutButton;

  // Initialize Mutable Arrays
  title = [NSMutableArray array];
  description = [NSMutableArray array];
  type = [NSMutableArray array];
  active = [NSMutableArray array];
  courseid = [NSMutableArray array];
  start = [NSMutableArray array];
  end = [NSMutableArray array];
  course = [NSMutableArray array];

  // Check if Token is valid, if yes fill table with data
  [self reloadData];
  [[self navigationItem]
      setBackBarButtonItem:[[UIBarButtonItem alloc]
                               initWithTitle:@""
                                       style:UIBarButtonItemStylePlain
                                      target:nil
                                      action:nil]];
}

- (void)viewDidAppear:(BOOL)animated {
  [super viewDidAppear:animated];
  [self reloadData];
}

// reloads table data
- (void)reloadData {
  if ([AppData checkToken]) {
    classes = [AppData getAppointments];
    courses = [AppData getCourses];
    long classesA = [classes count];
    if (classesA != 0) {
      data = YES;
      [self updateClasses:classes courses:courses];
    } else {
      data = NO;
    }
  }
  [self.tableView reloadData];
}

- (void)showLoginScreen:(BOOL)animated {

  // Get login screen from storyboard and present it
  UIStoryboard *storyboard =
      [UIStoryboard storyboardWithName:@"Main" bundle:nil];
  UIViewController *LoginViewController =
      [storyboard instantiateViewControllerWithIdentifier:@"loginScreen"];
  [self presentViewController:LoginViewController animated:YES completion:nil];
}

- (void)didReceiveMemoryWarning {
  [super didReceiveMemoryWarning];
  // Dispose of any resources that can be recreated.
}

// Logout Button, returns User to Login Screen
- (void)buttonTapped:(UIButton *)sender {
  NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
  [defaults setValue:0 forKey:@"userID"];
  [defaults setValue:0 forKey:@"token"];
  [defaults setValue:0 forKey:@"externalService"];
  [defaults setValue:0 forKey:@"validTime"];
  [defaults synchronize];
  UIStoryboard *storyboard =
      [UIStoryboard storyboardWithName:@"Main" bundle:nil];
  UIViewController *LoginViewController =
      [storyboard instantiateViewControllerWithIdentifier:@"loginScreen"];
  [self presentViewController:LoginViewController animated:YES completion:nil];
}
#pragma mark - Table view data source

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
  UILabel *messageLabel = [[UILabel alloc]
      initWithFrame:CGRectMake(0, 0, self.view.bounds.size.width,
                               self.view.bounds.size.height)];

  messageLabel.text =
      @"Keine Daten gefunden. Zum Aktualisieren herunterziehen.";
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

- (NSInteger)tableView:(UITableView *)tableView
    numberOfRowsInSection:(NSInteger)section {
  return classes.count;
}

- (UITableViewCell *)tableView:(UITableView *)tableView
         cellForRowAtIndexPath:(NSIndexPath *)indexPath {

  // Fill each cell of the Table with proper values
  TableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:@"Cell"
                                                        forIndexPath:indexPath];
  cell.cellTitle.text = course[indexPath.row];
  cell.cellDescription.text = [NSString
      stringWithFormat:@"%@(%@)", title[indexPath.row], type[indexPath.row]];
  cell.cellTimes.text = [NSString
      stringWithFormat:@"%@-%@", start[indexPath.row], end[indexPath.row]];
  if ([active[indexPath.row] isEqualToString:@"1"]) {
    cell.cellLocked.image = [UIImage imageNamed:@"lock_open.png"];
  } else {
    cell.cellLocked.image = [UIImage imageNamed:@"lock.png"];
  }

  // Configure the cell...

  return cell;
}

- (void)refresh:(UIRefreshControl *)refreshControl {
  // Refresh the List of currently active classes and courses
  classes = [AppData getAppointments];
  if (title != nil) {
    [title removeAllObjects];
    [description removeAllObjects];
    [active removeAllObjects];
    [courseid removeAllObjects];
    [start removeAllObjects];
    [end removeAllObjects];
    [course removeAllObjects];
  }
  long classesA = [classes count];
  if (classesA != 0) {
    data = YES;
    [self updateClasses:classes courses:courses];
  } else {
    data = NO;
  }
  [self.tableView reloadData];
  [refreshControl endRefreshing];
}

- (void)updateClasses:(NSArray *)classes courses:(NSArray *)courses {
  NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
  for (CourseObject *course in courses) {
    NSString *test = [NSString stringWithFormat:@"%i", course.Id];
    [defaults setValue:[NSString stringWithFormat:@"%@", course.fullname]
                forKey:test];
  }
  for (ClassObject *class in classes) {

    NSString *aiD = [NSString stringWithFormat:@"%d", class.Id];
    NSString *pin = [NSString stringWithFormat:@"%d", class.pin];
    NSString *starte = class.startdate;
    NSString *ende = class.enddate;
    [course
        addObject:[defaults
                      valueForKey:[NSString
                                      stringWithFormat:@"%@", class.courseid]]];
    [title addObject:class.title];
    [description addObject:class.currentdescription];
    [type addObject:class.type];
    [active addObject:pin];
    [courseid addObject:aiD];
    [start addObject:starte];
    [end addObject:ende];
  }
}

#pragma mark - Navigation

// In a storyboard-based application, you will often want to do a little
// preparation before navigation
- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
  if ([[segue identifier] isEqualToString:@"showDetail"]) {
    DetailViewController *detailView = [segue destinationViewController];

    NSIndexPath *myIndexPath = [self.tableView indexPathForSelectedRow];

    int row = (int)[myIndexPath row];
    detailView.detailModal = @[
      title[row], description[row], active[row], courseid[row], start[row],
      end[row], course[row], type[row]
    ];
  }
}

@end
