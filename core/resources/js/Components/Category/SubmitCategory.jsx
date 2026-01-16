import { useState, useEffect } from "react";
import axios from "axios";
import InputLabel from "../Utilities/InputLabel";
import Input from "../Utilities/Input";
import InputError from "../Utilities/InputError";
import Dropdown from "../Utilities/Dropdown";
import Button from "../Utilities/Button";
import transactionTypes from "../../../data/transaction_types.json";

const SubmitCategory = ({ category = null, onSuccess }) => {
    const isEditing = Boolean(category);

    const [name, setName] = useState("");
    const [type, setType] = useState("expense");
    const [color, setColor] = useState("#4f46e5");
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (isEditing) {
            setName(category.name);
            setType(category.type);
            setColor(category.color);
        }
    }, [category]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        const payload = {
            name,
            type,
            color,
        };

        const url = isEditing
            ? `/api/categories/${category.id}`
            : `/api/categories`;

        const method = isEditing ? "put" : "post";

        try {
            await axios[method](url, payload, {
                withCredentials: true,
            });

            if (onSuccess) onSuccess();
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            } else {
                console.error("Unexpected error:", error);
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="rounded-lg space-y-6">
            <h2 className="text-xl font-semibold text-gray-800">
                {isEditing ? "Edit Category" : "Create Category"}
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

            {/* Type */}
            <div>
                <Dropdown
                    label="Type"
                    value={type}
                    onChange={(option) => setType(option.value)} 
                    options={transactionTypes.map((t) => ({
                        label: t.name,
                        value: t.name.toLowerCase(),
                    }))}
                />
                {errors.type && <InputError message={errors.type} />}
            </div>

            {/* Color */}
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
                {loading
                    ? isEditing
                        ? "Saving..."
                        : "Creating..."
                    : isEditing
                    ? "Save Changes"
                    : "Create Category"}
            </Button>
        </form>
    );
};

export default SubmitCategory;