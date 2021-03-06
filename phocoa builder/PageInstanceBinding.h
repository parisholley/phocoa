//
//  PageInstanceBinding.h
//  PHOCOA Builder
//
//  Created by Alan Pinstein on 10/31/05.
//  Copyright 2005 __MyCompanyName__. All rights reserved.
//

#import <Cocoa/Cocoa.h>

#import "PageInstanceBindingOption.h"

@interface PageInstanceBinding : NSManagedObject {

}

+ (PageInstanceBinding*) bindProperty: (NSString*) bindProperty withSetup: (NSDictionary*) bindingSetup context: (NSManagedObjectContext*) context;
- (NSDictionary*) bindingAsDictionary;

@end
