-- to start over
DELETE FROM defendants_events WHERE defendant_id IN (22668,22671);
DELETE FROM defendants_requests WHERE defendant_id IN (22668,22671);
DELETE FROM `defendant_names` WHERE id IN (22668,22671);
INSERT INTO `defendant_names` VALUES (22668,'José','Núñez Lora'),(22671,'José Luis','Núñez Lora');
INSERT INTO `defendants_events` VALUES (109431,22668),(109615,22668),(109637,22668),(111307,22668),(109432,22671),(109434,22671),(110319,22671),(110564,22671),(110633,22671),(111137,22671),(111160,22671),(112264,22671),(112267,22671);
INSERT INTO `defendants_requests` VALUES (22668,19046),(22671,18745),(22671,18829);
