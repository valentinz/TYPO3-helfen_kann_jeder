# HelfenKannJeder TYPO3 Extension
This is the main part of the German website HelfenKannJeder.de. The goal of the project is recruiting of volunteer people to civil protection organizations.

Basically HelfenKannJeder has three main functions for consumers:
* Skill testing of interested persons with mapping to a civil protection organization.
* Showing all civil protection organizations in a map.
* List of all organizations.

For civil protection organizations there exists the possibility to register their organization. Heads clustered around the organizations can prove the registrations and accept them.

If you are interested in this project, you can write an mail to valentin.zickner@helfenkannjeder.de for an install introduction.

## Installation

For easier installation a vagrant file was created. Simply type `vagrant up` in the repository root folder to startup the vagrant machine.
During startup the machine install a TYPO3 instance with the credentials `admin` and `password`. In addition
it automaticly install the helfen_kann_jeder extension.

## Package Creation

``make zip``
