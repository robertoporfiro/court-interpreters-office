/*
SQL for importing interpreters, languages and interpreters_languages
*/

/* FIRST purge interpreters_languages, interpreters, people and users */

DELETE FROM interpreters_languages;
DELETE FROM languages;
DELETE FROM users;
DELETE i, p FROM interpreters i JOIN people p ON i.id = p.id ;

/* INSERT INTO people table */
INSERT INTO people 
(id, lastname, firstname, middlename, office_phone, mobile_phone, email, active, hat_id,discr)

(SELECT interp_id, lastname, firstname, middlename, office, mobile, 
    IF(email <> "",email, NULL), 
    IF(active = 'Y', 1, 0), 
    IF(freelance = "Y", 3, 1),
    "interpreter"
FROM dev_interpreters.interpreters ORDER BY interp_id);
/* followed by interpreters table */
INSERT INTO interpreters (
id, phone, security_clearance_date, contract_expiration_date,fingerprint_date, oath_date
)
(SELECT interp_id, home, 
    IF (security_clearance = "0000-00-00",NULL,security_clearance), 
    IF (contract_expiration = "0000-00-00",NULL,contract_expiration),
    fingerprinted, 
    oath  
FROM dev_interpreters.interpreters ORDER BY interp_id);

INSERT INTO languages (SELECT * FROM dev_interpreters.languages);

INSERT INTO interpreters_languages (SELECT interp_id, lang_id,
    CASE fed_cert
       WHEN 'N/A' THEN null
       WHEN 'Y' THEN 1 
       WHEN 'N' THEN 0
    END
    FROM dev_interpreters.interp_languages
);