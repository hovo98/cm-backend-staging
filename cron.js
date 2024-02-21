const cron = require('node-cron');
const exec = require('child_process').exec;

// Runs every minute
const schedule = '* * * * *';
const command = 'php artisan schedule:run';

cron.schedule(schedule, function() {
    exec(command,
        function(error, stdout) {
            if (stdout) {
                console.log(stdout);
            }
            if (error) {
                console.log(error);
            }
        });
});
