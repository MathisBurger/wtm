// Prevents additional console window on Windows in release, DO NOT REMOVE!
#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]
use std::env;
use std::os::windows::process::CommandExt;
use std::process::Command;
use tauri::Manager;
use tauri::SystemTrayEvent;
use tauri::{CustomMenuItem, SystemTray, SystemTrayMenu, SystemTrayMenuItem};

const CREATE_NO_WINDOW: u32 = 0x08000000;

fn get_url() -> String {
    if env::var("APP_MODE").is_ok() && env::var("APP_MODE").unwrap() == "dev" {
        return "http://localhost:8000".to_string();
    } else {
        return "http://zeiterfassung.ad.dreessen.biz".to_string();
    }
}

fn get_hostname() -> String {
    let output = Command::new("hostname")
        .creation_flags(CREATE_NO_WINDOW)
        .output()
        .unwrap();
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
        .creation_flags(CREATE_NO_WINDOW)
        .output()
        .expect("Cannot check for rdp sessions");
    let content = String::from_utf8_lossy(&output.stdout).to_string();
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

#[tauri::command]
async fn open_docs(handle: tauri::AppHandle) {
    let docs_window = tauri::WindowBuilder::new(
        &handle,
        "external",
        tauri::WindowUrl::External(get_url().parse().unwrap()),
    )
    .build()
    .unwrap();
}

fn main() {
    let current_action = get_current_action();
    let action_name = match current_action.as_str() {
        "checkIn" => "Einstempeln",
        "checkOut" => "Ausstempeln",
        _ => "Fehler",
    };
    let quit = CustomMenuItem::new("quit".to_string(), "Beenden");
    let hide = CustomMenuItem::new("hide".to_string(), "Ausblenden");
    let checkInOut = CustomMenuItem::new("checkInOut".to_string(), action_name);
    let adminOpen = CustomMenuItem::new("adminOpen".to_string(), "Administration");
    let tray_menu = SystemTrayMenu::new()
        .add_item(quit)
        .add_native_item(SystemTrayMenuItem::Separator)
        .add_item(hide)
        .add_item(checkInOut)
        .add_item(adminOpen);
    let system_tray = SystemTray::new().with_menu(tray_menu);
    tauri::Builder::default()
        .system_tray(system_tray)
        .on_system_tray_event(|app, event| match event {
            SystemTrayEvent::MenuItemClick { id, .. } => {
                let item_handle = app.tray_handle().get_item(&id);
                match id.as_str() {
                    "quit" => {
                        std::process::exit(0);
                    }
                    "hide" => {
                        let window = app.get_window("main").unwrap();
                        window.hide().unwrap();
                    }
                    "checkInOut" => {
                        //let window = app.get_window("main").unwrap();
                        //window.show().unwrap();
                        let current_action = get_current_action();
                        if current_action == "checkIn" {
                            check_in();
                        } else if current_action == "checkOut" {
                            check_out();
                        }
                        let current_action2 = get_current_action();
                        let action_name = match current_action2.as_str() {
                            "checkIn" => "Einstempeln",
                            "checkOut" => "Ausstempeln",
                            _ => "Fehler",
                        };
                        item_handle.set_title(action_name).unwrap();
                    }
                    "adminOpen" => {
                        let window = tauri::WindowBuilder::new(
                            app,
                            "external",
                            tauri::WindowUrl::External(get_url().parse().unwrap()),
                        )
                        .build()
                        .unwrap();
                        window.set_title("Administration");
                    }
                    _ => {}
                }
            }
            _ => {}
        })
        .invoke_handler(tauri::generate_handler![
            get_current_action,
            check_in,
            check_out,
            is_rdp,
            open_docs
        ])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
