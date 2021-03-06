//
//  ModuleMainWindowController.h
//  PHOCOA Builder
//
//  Created by Alan Pinstein on 10/30/05.
//  Copyright 2005 __MyCompanyName__. All rights reserved.
//

#import <Cocoa/Cocoa.h>


@interface ModuleMainWindowController : NSWindowController {
    IBOutlet    id saveProgress;
    IBOutlet    id sharedInstancesController;
    IBOutlet    id pageInstancesController;
    IBOutlet    id pageInstanceBindingController;
    IBOutlet    id pageController;
    IBOutlet    id pageInstanceTablePropertyColumn;
    
    IBOutlet    id sharedInstancesInspectorWindow;
    IBOutlet    id pageInstancesInspectorWindow;
}

- (void) startSaveProgress;
- (void) stopSaveProgress;

- (IBAction) insertBinding: (id) sender;

- (void) showSharedInstancesInspector;
- (void) showPageInstancesInspector;

@end
