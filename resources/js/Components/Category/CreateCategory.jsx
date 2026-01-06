import { useState } from "react";
import InputLabel from "../Utilities/InputLabel";
import Input from "../Utilities/Input";
import InputError from "../Utilities/InputError";
import Dropdown from "../Utilities/Dropdown";
import Button from "../Utilities/Button";
import transactionTypes from "../../../data/transaction_types.json";

export default function CreateCategory({ onCreated }) {
    const [name, setName] = useState("");
    const [type, setType] = useState("expense");
    const [color, setColor] = useState("#4f46e5");
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            const response = await fetch("/api/categories", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ name, type, color }),
            });

            if (response.status === 201) {
                const data = await response.json();
                onCreated && onCreated(data);

                setName("");
                setType("expense");
                setColor("#4f46e5");
            } else if (response.status === 422) {
                const data = await response.json();
                setErrors(data.errors || {});
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <form
            onSubmit={handleSubmit}
            className="bg-white p-6 rounded-lg shadow-md space-y-6 max-w-md"
        >
            <h2 className="text-xl font-semibold text-gray-800">
                Create Category
            </h2>

            {/* Name */}
            <div>
                <InputLabel value="Name" />
                <Input
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
                {errors.name && <InputError message={errors.name} />}
            </div>

            {/* Type (Dropdown) */}
            <div>
                <Dropdown
                    label="Type"
                    value={type}
                    onChange={setType}
                    options={transactionTypes.map(t => ({
                        label: t.name,
                        value: t.name.toLowerCase(),
                    }))}
                />


                {errors.type && <InputError message={errors.type} />}
            </div>

            {/* Colour Picker */}
            <div>
                <InputLabel value="Colour" />

                <div className="flex items-center gap-4 mt-2">
                    <Input
                        type="color"
                        value={color}
                        onChange={(e) => setColor(e.target.value)}
                        className="h-10 w-16 p-0 cursor-pointer"
                    />

                    <span
                        className="px-3 py-1 rounded text-white text-sm"
                        style={{ backgroundColor: color }}
                    >
                        {color}
                    </span>
                </div>

                {errors.color && <InputError message={errors.color} />}
            </div>

            {/* Submit */}
            <Button
                type="submit"
                disabled={loading}
                variant="primary"
                className="w-full"
            >
                {loading ? "Creating..." : "Create Category"}
            </Button>
        </form>
    );
}