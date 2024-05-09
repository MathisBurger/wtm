import { useEffect, useState } from "react";
import { invoke } from "@tauri-apps/api/tauri";
import "./App.css";
import Message, { MessageProps } from "./Message";

function App() {
  const [currentAction, setCurrentAction] = useState("");
  const [message, setMessage] = useState<MessageProps | null>(null);

  async function getCurrentAction() {
    if (await invoke("is_rdp")) {
      setMessage({
        message: "Sie können sich nicht aus der Ferne über RDP einstempeln.",
        messageStatus: "alert-danger",
      });
      return;
    }
    let result = await invoke("get_current_action");
    setCurrentAction(result as string);
  }

  async function checkIn() {
    let jsonContent = await invoke("check_in");
    setMessage(JSON.parse(jsonContent as string));
    setTimeout(() => {
      setMessage(null);
    }, 5000);
    setCurrentAction(await invoke("get_current_action"));
  }

  async function checkOut() {
    let jsonContent = await invoke("check_out");
    setMessage(JSON.parse(jsonContent as string));
    setTimeout(() => {
      setMessage(null);
    }, 5000);
    setCurrentAction(await invoke("get_current_action"));
  }

  useEffect(() => {
    getCurrentAction();
  }, []);

  console.log(currentAction);

  return (
    <div className="container">
      {message && (
        <Message
          message={message.message}
          messageStatus={message.messageStatus}
        />
      )}
      {currentAction === "checkIn" && (
        <button onClick={checkIn}>Einstempeln</button>
      )}
      {currentAction === "checkOut" && (
        <button onClick={checkOut}>Ausstempeln</button>
      )}
      {currentAction !== "checkIn" && currentAction !== "checkOut" && currentAction !== "" && (
        <Message message={currentAction} messageStatus="alert-danger" />
      )}
    </div>
  );
}

export default App;
