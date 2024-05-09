export interface MessageProps {
  message: string;
  messageStatus: string;
}

const Message = ({ message, messageStatus }: MessageProps) => {
  return <div className={"alert " + messageStatus}>{message}</div>;
};

export default Message;
