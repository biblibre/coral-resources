ALTER TABLE `Resource`
  ADD COLUMN `dateFirstIssueOnline` date default NULL,
  ADD COLUMN `numFirstVolOnline` int(11) default NULL,
  ADD COLUMN `numFirstIssueOnline` int(11) default NULL,
  ADD COLUMN `dateLastIssueOnline` date default NULL,
  ADD COLUMN `numLastVolOnline` int(11) default NULL,
  ADD COLUMN `numLastIssueOnline` int(11) default NULL,
  ADD COLUMN `firstAuthor` varchar(200) default NULL,
  ADD COLUMN `embargoInfo` varchar(200) default NULL,
  ADD COLUMN `coverageDepth` varchar(200) default NULL;

