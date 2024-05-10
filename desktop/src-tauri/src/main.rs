// Prevents additional console window on Windows in release, DO NOT REMOVE!
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::env;
use std::process::{Command, Stdio};

fn get_url() -> String {
    if env::var("APP_MODE").is_ok() && env::var("APP_MODE").unwrap() == "dev" {
        return "http://localhost:8000".to_string();
    } else {
        return "http://zeiterfassung.ad.dreessen.biz".to_string();
    }
}

fn get_hostname() -> String {
    let output = Command::new("hostname")
        .stdout(Stdio::piped())
        .output()
        .expect("Cannot check for hostname");
    return String::from_utf8_lossy(&output.stdout).to_string();
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

fn is_windows() -> bool {
    if cfg!(windows) {
        return true;
    }
    return false;
}

#[tauri::command]
fn is_rdp() -> bool {
    if !is_windows() {
        return false;
    }
    let output = Command::new("qwinsta")
        .args(["/"])
        .stdout(Stdio::piped())
        .output()
        .expect("Cannot check for rdp sessions");
    let content = String::from_utf8_lossy(&output.stdout);
    return content.contains("rdp-tcp#");
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
    let hostname = get_hostname();
    let formatted_url = format!("{host}/api/v1/check-in/{username}?format=json&device={hostname}");
    let resp = reqwest::blocking::get(formatted_url).expect("Request blocked");
    let text = resp.text().expect("Cannot get text");
    return text;
}

#[tauri::command]
fn check_out() -> String {
    let username = get_username();
    let host = get_url();
    let hostname = get_hostname();
    let formatted_url = format!("{host}/api/v1/check-out/{username}?format=json&device={hostname}");
    let resp = reqwest::blocking::get(formatted_url).expect("Request blocked");
    let text = resp.text().expect("Cannot get text");
    return text;
}

fn main() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![
            get_current_action,
            check_in,
            check_out,
            is_rdp,
        ])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
