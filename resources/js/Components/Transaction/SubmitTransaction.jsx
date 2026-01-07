import { useEffect, useState } from "react";
import InputLabel from "../Utilities/InputLabel";
import Input from "../Utilities/Input";
import InputError from "../Utilities/InputError";
import Dropdown from "../Utilities/Dropdown";
import Button from "../Utilities/Button";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowTrendUp, faArrowTrendDown } from "@fortawesome/free-solid-svg-icons";
import Alert from "../Utilities/Alert";
import RecurringRuleFields from "./ReccuringRuleFields";

const SubmitTransaction = ({initialValues = null, onSubmit, onSuccess, submitLabel = "Add Transaction"}) => {
    const today = new Date().toISOString().split("T")[0];

    const [categories, setCategories] = useState([]);
    const incomeCategories = categories.filter(c => c.type === "income");
    const expenseCategories = categories.filter(c => c.type === "expense");
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const [loadingCategories, setLoadingCategories] = useState(true);
    const [successMessage, setSuccessMessage] = useState("");
    const [showSuccess, setShowSuccess] = useState(false);

    const [amount, setAmount] = useState(initialValues?.amount ?? 0);
    const [selectedCategory, setSelectedCategory] = useState(initialValues?.category_id ?? null);
    const [date, setDate] = useState(initialValues?.date ?? today);
    const [description, setDescription] = useState(initialValues?.description ?? "");
    const [recurringRule, setRecurringRule] = useState(
        initialValues?.recurring_rule ?? {
            isRecurring: false,
            frequency: "monthly",
            interval: 1,
            months: [],
        }
    );


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
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            await onSubmit(
                {
                    amount,
                    category_id: selectedCategory,
                    date,
                    description,
                    recurringRule,
                },
                setErrors,
                setLoading,
            );


            // Reset form after success
            setAmount(0);
            setSelectedCategory(null);
            setDate(today);
            setDescription('');

            setSuccessMessage("Transaction added successfully!");

            if (typeof onSuccess == "function") {
                onSuccess();
            }
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            } else {
                console.error("Unexpected error", error);
            }
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        // Format YYYY-MM-DD for <input type="date" />
        const today = new Date().toISOString().split("T")[0];
        setDate(today);

        getCategories();
    }, []);

    useEffect(() => {
        if (initialValues) {
            setAmount(initialValues.amount ?? 0);
            setSelectedCategory(initialValues.category_id ?? null);
            setDate(initialValues.date ?? today);
            setDescription(initialValues.description ?? "");
            setRecurringRule(
                initialValues.recurring_rule ?? {
                    isRecurring: false,
                    frequency: "monthly",
                    interval: 1,
                    months: [],
                }
            );
        }
    }, [initialValues]);

    return (
        <div>
            <form
                onSubmit={handleSubmit}
                className="bg-white p-6 rounded-lg shadow-md space-y-6 max-w-md"
            >
                <Alert
                    message={successMessage}
                    type="success"
                    onClear={() => setSuccessMessage("")}
                />

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
                        options={[
                            // Income first
                            ...incomeCategories.map(c => ({
                                label: (
                                    <div className="flex items-center gap-2">
                                        <FontAwesomeIcon
                                            icon={faArrowTrendUp}
                                            className="text-green-600"
                                        />
                                        <span>{c.name}</span>
                                    </div>
                                ),
                                value: c.id,
                            })),

                            // Expense next
                            ...expenseCategories.map(c => ({
                                label: (
                                    <div className="flex items-center gap-2">
                                        <FontAwesomeIcon
                                            icon={faArrowTrendDown}
                                            className="text-red-600"
                                        />
                                        <span>{c.name}</span>
                                    </div>
                                ),
                                value: c.id,
                            })),
                        ]}
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

                <div>
                    <RecurringRuleFields
                        value={recurringRule}
                        onChange={setRecurringRule}
                    />
                </div>

                {/* Submit */}
                <Button
                    type="submit"
                    disabled={loading}
                    variant="primary"
                    className="w-full"
                >
                    {loading ? "Saving..." : submitLabel}
                </Button>
            </form>
        </div>
    )
}

export default SubmitTransaction;