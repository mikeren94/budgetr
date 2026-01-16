import { useEffect, useState } from "react";

const Alert = ({ message, type = "success", duration = 3000, onClear }) => {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (!message) return;

        // Fade in immediately
        setVisible(true);

        // Start fade-out after duration
        const fadeTimer = setTimeout(() => {
            setVisible(false);
        }, duration);

        // Remove from DOM AFTER fade-out completes (duration + 500ms)
        const removeTimer = setTimeout(() => {
            onClear?.();
        }, duration + 500);

        return () => {
            clearTimeout(fadeTimer);
            clearTimeout(removeTimer);
        };
    }, [message, duration, onClear]);

    if (!message) return null;

    const baseStyles =
        "mb-4 px-4 py-2 rounded-md border transition-opacity duration-500";

    const typeStyles = {
        success: "bg-green-100 border-green-300 text-green-800",
        error: "bg-red-100 border-red-300 text-red-800",
        warning: "bg-yellow-100 border-yellow-300 text-yellow-800",
        info: "bg-blue-100 border-blue-300 text-blue-800",
    };

    return (
        <div className={`${baseStyles} ${typeStyles[type]} ${visible ? "opacity-100" : "opacity-0"}`}>
            {message}
        </div>
    );
}

export default Alert;