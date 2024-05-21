const jsonConfig = require("./src-tauri/tauri.conf.json");
const fs = require("fs");


let version = jsonConfig.package.version;
let sigContent = fs.readFileSync(`src-tauri\\target\\release\\bundle\\msi\\Zeiterfassung (WTM)_${version}_x64_en-US.msi.zip.sig`,'utf8');

let json = {
    version: version,
    notes: "new release",
    pub_date: new Date(),
    platforms: {
        'windows-x86_64': {
            signature: sigContent,
            url: `https://github.com/MathisBurger/wtm/releases/download/v${version}/Zeiterfassung.WTM._${version}_x64_en-US.msi.zip`
        }
    }
}

fs.writeFile('./latest-release.json', JSON.stringify(json), err => {
    if (err) {
        console.error(err);
    } else {
        console.log("File written successfully")
    }
});
