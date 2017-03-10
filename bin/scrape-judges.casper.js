/**
 * 
 * screen-scraper for CasperJs to get SDNY judge courtrooms
 * Usage: casperjs <scriptname>.js
 * 
 */

var casper = require('casper').create({
    verbose:true //, clientScripts: ["../public/js/lib/jquery.min.js"]
});

// var fs = require('fs');
// var fileUrl = "file:///opt/www/court-interpreters-office/public/judges.html";

var baseUrl = "http://nysd.uscourts.gov";

casper.start(baseUrl+ '/judges/District', function() {
  
   this.waitForSelector('table.judge_info'); 
   // get an object in the form { judge_name : url, ... }
   links = this.evaluate(function(){       
       var links = jQuery("table.judge_info tbody tr td a");
       var baseUrl = "http://nysd.uscourts.gov/";
       $return = {};
       links.each(function(i,e){
           $return[e.textContent.trim()] = baseUrl+e.getAttribute('href');
       });
       return $return;       
   });
});

casper.then(function(){   
    for(var judge in links) {
       var url = links[judge];
       // and now, use a IIFE and tear your hair out no more! 
       // http://stackoverflow.com/questions/24360993/casperjs-iterating-through-urls
       // https://groups.google.com/forum/#!topic/casperjs/n_zXlxiPMtk
       (function(url,name){
           casper.thenOpen(url,function(){
               this.echo(name+ ":  "+this.getCurrentUrl());
               data = this.evaluate(function() {
                    var text = '';
                    var elements = jQuery("table#text.main tbody tr.whiteback td table tbody tr td p:lt(2)");
                    elements.each(function (i, e) {
                        text += e.innerHTML.replace(/\s*<br *\/?>\s*/g, "\n").trim();
                        text += "\n";
                    });
                    return text;
                });
               this.echo(data);
           });})
       (url,judge);       
    }  
});
casper.run();
