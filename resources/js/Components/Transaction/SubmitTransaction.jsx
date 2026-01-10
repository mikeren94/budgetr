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

const SubmitTransaction = ({
    initialValues = null,
    onSubmit,
    onSuccess,
    submitLabel = "Add Transaction"
}) => {
    const today = new Date().toISOString().split("T")[0];

    const [categories, setCategories] = useState([]);
    const incomeCategories = categories.filter(c => c.type === "income");
    const expenseCategories = categories.filter(c => c.type === "expense");

    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const [loadingCategories, setLoadingCategories] = useState(true);
    const [successMessage, setSuccessMessage] = useState("");

    const [amount, setAmount] = useState(initialValues?.amount ?? 0);
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [date, setDate] = useState(initialValues?.date ?? today);
    const [description, setDescription] = useState(initialValues?.description ?? "");
    const [paid, setPaid] = useState(initialValues?.paid ?? true);

    const [recurringRule, setRecurringRule] = useState(
        initialValues?.recurring_rule ?? {
            isRecurring: false,
            frequency: "monthly",
            interval: 1,
            months: [],
        }
    );

    const [coverageEndDate, setCoverageEndDate] = useState(
        initialValues?.coverage_end_date ?? ""
    );

    const hasRecurringRule = recurringRule?.isRecurring;

    // Build dropdown options
    const categoryOptions = [
        ...incomeCategories.map(c => ({
            label: (
                <div className="flex items-center gap-2">
                    <FontAwesomeIcon icon={faArrowTrendUp} className="text-green-600" />
                    <span>{c.name}</span>
                </div>
            ),
            value: c.id,
        })),
        ...expenseCategories.map(c => ({
            label: (
                <div className="flex items-center gap-2">
                    <FontAwesomeIcon icon={faArrowTrendDown} className="text-red-600" />
                    <span>{c.name}</span>
                </div>
            ),
            value: c.id,
        })),
    ];

    const getCategories = async () => {
        try {
            const response = await axios.get("/api/categories", {
                withCredentials: true
            });

            setCategories(response.data.data);
        } catch (error) {
            console.error("Failed to load categories", error);
        } finally {
            setLoadingCategories(false);
        }
    };

    // Load categories
    useEffect(() => {
        getCategories();
    }, []);

    // Map initialValues.category_id → dropdown option object
    useEffect(() => {
        if (!initialValues || categories.length === 0) return;

        const match = categoryOptions.find(opt => opt.value === initialValues.category_id);
        setSelectedCategory(match ?? null);
    }, [categories, initialValues]);

    // Auto-set "paid" based on category type
    useEffect(() => {
        if (!selectedCategory || categories.length === 0) return;

        const category = categories.find(c => c.id === selectedCategory.value);
        if (!category) return;

        if (!initialValues) {
            setPaid(category.is_bill ? false : true);
        }
    }, [selectedCategory, categories]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        const payload = {
            amount,
            category_id: selectedCategory?.value ?? null,
            date,
            description,
            coverage_end_date: coverageEndDate || null,
            paid,
        };

        if (recurringRule.isRecurring) {
            payload.recurringRule = recurringRule;
        }

        const success = await onSubmit(payload, setErrors, setLoading);

        if (success) {
            if (!initialValues) {
                setAmount(0);
                setSelectedCategory(null);
                setDate(today);
                setDescription('');
            }

            setSuccessMessage(
                initialValues
                    ? "Transaction updated successfully!"
                    : "Transaction added successfully!"
            );

            if (typeof onSuccess === "function") {
                onSuccess();
            }
        }

        setLoading(false);
    };

    return (
        <div>
            <form onSubmit={handleSubmit} className="p-6 rounded-lg space-y-6">
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

                {/* Category */}
                <div>
                    <Dropdown
                        label="Category"
                        value={selectedCategory}
                        onChange={setSelectedCategory}
                        options={categoryOptions}
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

                {/* Recurring Rule */}
                <RecurringRuleFields
                    value={recurringRule}
                    onChange={setRecurringRule}
                />

                {hasRecurringRule && (
                    <div>
                        <InputLabel value="Coverage End Date" />
                        <Input
                            type="date"
                            value={coverageEndDate}
                            onChange={(e) => setCoverageEndDate(e.target.value)}
                        />
                        {errors.coverage_end_date && (
                            <InputError message={errors.coverage_end_date} />
                        )}
                    </div>
                )}

                {/* Paid */}
                <div>
                    <InputLabel value="Paid" />
                    <div className="flex items-center gap-2">
                        <Input
                            type="checkbox"
                            checked={paid}
                            onChange={(e) => setPaid(e.target.checked)}
                            className="h-4 w-4"
                        />
                        <span className="text-gray-700">Mark as paid</span>
                    </div>
                    {errors.paid && <InputError message={errors.paid} />}
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
    );
};

export default SubmitTransaction;