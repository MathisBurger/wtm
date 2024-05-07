import {useEffect, useState} from "react";
import { invoke } from "@tauri-apps/api/tauri";
import "./App.css";

function App() {
  const [currentAction, setCurrentAction] = useState("");

  async function getCurrentAction() {
    let result = await invoke("get_current_action");
    setCurrentAction(result as string);
  }

  async function checkIn() {
      await invoke("check_in");
      setTimeout(async () => {
        setCurrentAction(await invoke("get_current_action"));
      }, 1000)
  }

  async function checkOut() {
    await invoke("check_out");
    setTimeout(async () => {
      setCurrentAction(await invoke("get_current_action"));
    }, 1000)
  }

  useEffect(() => {
    getCurrentAction();
  }, []);

  console.log(currentAction);

  return (
    <div className="container">
      {currentAction === "checkIn" && (
          <button onClick={checkIn}>Einstempeln</button>
      )}
      {currentAction === "checkOut" && (
          <button onClick={checkOut}>Ausstempeln</button>
      )}
      {currentAction}
    </div>
  );
}

export default App;
