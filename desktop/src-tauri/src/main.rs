

// Prevents additional console window on Windows in release, DO NOT REMOVE!
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::env;
use url::Url;
use url_open::UrlOpen;

/// Gets the username of the current system
fn get_username() -> String {
    return match env::var("USERNAME") {
        Ok(v) => v,
        Err(e) => env::var("USER").unwrap()
    }
}

#[tauri::command]
fn get_current_action() -> String {
    let username = get_username();
    let formatted_url = format!("http://zeiterfassung.ad.dreessen.biz/api/v1/required-action/{username}");
    let resp = reqwest::blocking::get(formatted_url).expect("Request blocked");
    let text = resp.text().expect("Cannot get text");
    return text;
}

#[tauri::command]
fn check_in() {
    let username = get_username();
    let formatted_url = format!("http://zeiterfassung.ad.dreessen.biz/api/v1/check-in/{username}");
    Url::parse(&formatted_url).unwrap().open();
}

#[tauri::command]
fn check_out() {
    let username = get_username();
    let formatted_url = format!("http://zeiterfassung.ad.dreessen.biz/api/v1/check-out/{username}");
    Url::parse(&formatted_url).unwrap().open();
}

fn main() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![get_current_action, check_in, check_out])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
