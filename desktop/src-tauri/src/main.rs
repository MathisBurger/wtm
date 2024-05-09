// Prevents additional console window on Windows in release, DO NOT REMOVE!
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::env;
use url::Url;
use url_open::UrlOpen;

fn get_url() -> String {
    if env::var("APP_MODE").is_ok() && env::var("APP_MODE").unwrap() == "dev" {
        return "http://localhost:8000".to_string();
    } else {
        return "http://zeiterfassung.ad.dreessen.biz".to_string();
    }
}

/// Gets the username of the current system
fn get_username() -> String {
    if env::var("APP_MODE").is_ok() && env::var("APP_MODE").unwrap() == "dev" {
        return "demouser".to_string();
    }
    return match env::var("USERNAME") {
        Ok(v) => v,
        Err(_e) => env::var("USER").unwrap(),
    };
}

#[tauri::command]
fn get_current_action() -> String {
    let username = get_username();
    let host = get_url();
    let formatted_url = format!("{host}/api/v1/required-action/{username}");
    let resp = reqwest::blocking::get(formatted_url).expect("Request blocked");
    let text = resp.text().expect("Cannot get text");
    return text;
}

#[tauri::command]
fn check_in() -> String {
    let username = get_username();
    let host = get_url();
    let formatted_url = format!("{host}/api/v1/check-in/{username}?format=json");
    let resp = reqwest::blocking::get(formatted_url).expect("Request blocked");
    let text = resp.text().expect("Cannot get text");
    return text;
}

#[tauri::command]
fn check_out() -> String {
    let username = get_username();
    let host = get_url();
    let formatted_url = format!("{host}/api/v1/check-out/{username}?format=json");
    let resp = reqwest::blocking::get(formatted_url).expect("Request blocked");
    let text = resp.text().expect("Cannot get text");
    return text;
}

fn main() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![
            get_current_action,
            check_in,
            check_out
        ])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
