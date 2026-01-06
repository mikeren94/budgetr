import { useEffect, useState } from "react";
import InputLabel from "../Utilities/InputLabel";
import Input from "../Utilities/Input";
import InputError from "../Utilities/InputError";
import Dropdown from "../Utilities/Dropdown";
import Button from "../Utilities/Button";

const CreateTransaction = () => {
    const [amount, setAmount] = useState(0);
    const [categories, setCategories] = useState([]);
    const [date, setDate] = useState('');
    const [description, setDescription] = useState('');
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [loadingCategories, setLoadingCategories] = useState(true);
    
    const getCategories = async () => {
        try {
            const response = await axios.get("/api/categories", {
                withCredentials: true
            });

            // Laravel API Resources wrap data in { data: [...] }
            setCategories(response.data.data);
        } catch (error) {
            console.error("Failed to load categories", error);
        } finally {
            setLoadingCategories(false);
        }
    };

    
    const handleSubmit = async (e) => {
        console.log('submitting');
    }

    useEffect(() => {
        getCategories();
    }, []);

    return (
        <form
            onSubmit={handleSubmit}
            className="bg-white p-6 rounded-lg shadow-md space-y-6 max-w-md"
        >
            <h2 className="text-xl font-semibold text-gray-800">
                Add Transaction
            </h2>

            {/* Amount */}
            <div>
                <InputLabel value="Amount" />
                <Input
                    type="number"
                    step="0.01"
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                />
                {errors.amount && <InputError message={errors.amount} />}
            </div>

            {/* Category (Dropdown) */}
            <div>
                <Dropdown
                    label="Category"
                    value={selectedCategory}
                    onChange={setSelectedCategory}
                    options={categories.map(c => ({
                        label: c.name,
                        value: c.id, // or the whole object if you prefer
                    }))}
                    placeholder={loadingCategories ? "Loading..." : "Select a category"}
                />

                {errors.category_id && <InputError message={errors.category_id} />}
            </div>

            {/* Date */}
            <div>
                <InputLabel value="Date" />
                <Input
                    type="date"
                    value={date}
                    onChange={(e) => setDate(e.target.value)}
                />
                {errors.date && <InputError message={errors.date} />}
            </div>

            {/* Description */}
            <div>
                <InputLabel value="Description" />
                <Input
                    type="text"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                />
                {errors.description && <InputError message={errors.description} />}
            </div>

            {/* Submit */}
            <Button
                type="submit"
                disabled={loading}
                variant="primary"
                className="w-full"
            >
                {loading ? "Adding..." : "Add Transaction"}
            </Button>
        </form>
    )
}

export default CreateTransaction;