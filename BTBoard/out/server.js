const express = require("express");
const app = express();
process.env.PWD = process.cwd()
app.use(express.static(process.env.PWD + '/public'))
app.listen(8070);
app.get('/', function (req, res) {
    //console.log(`GET index.html`);
    res.sendFile(`${__dirname}/htmlView.html`); //fill full path

});