#ACME REPO SCANNER

The purpose of this application is to: 

 * Ping a list of repositories to check for latest commits and match those date against a value in a database

 * Ping a php git-status.php script in a repo and notify configured email if the repo is dirty

Take a look at the settings.php.tpl to get an understanding of what values the RepoScannerNeeds

**mysql**

create the following table:

``` 

CREATE TABLE IF NOT EXISTS `repositories` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `account_id` int(8) NOT NULL,
  `name` varchar(72) NOT NULL,
  `url` varchar(256) NOT NULL,
  `project_location` varchar(256) NOT NULL,
  `last_synched` datetime NOT NULL,
  `local_backup_location` varchar(256) NOT NULL,
  `repo_status_node` varchar(246) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

```
