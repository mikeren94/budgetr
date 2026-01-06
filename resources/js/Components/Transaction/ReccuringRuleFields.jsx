import Input from "../Utilities/Input";
import Dropdown from "../Utilities/Dropdown";
import MonthSelector from "../Utilities/MonthSelector";
const ReccuringRuleFields = ({ value, onChange, errors }) => {
    const { isRecurring, frequency, interval, months } = value;
    const update = (field, val) =>
        onChange({ ...value, [field]: val });

    return (
        <div className="space-y-4 mt-4 p-4 border rounded-lg bg-gray-50">

            {/* Toggle */}
            <label className="flex items-center gap-2 cursor-pointer">
                <Input 
                    type="checkbox"
                    checked={isRecurring}
                    onChange={(e) => update("isRecurring", e.target.checked)}
                />

                <span className="font-medium text-gray-700">
                    Make this recurring
                </span>
            </label>

            {isRecurring && (
                <>
                    {/* Frequency */}
                    <Dropdown
                        label="Frequency"
                        value={frequency}
                        onChange={(v) => update("frequency", v)}
                        options={[
                            { label: "Monthly", value: "monthly" },
                            { label: "Yearly", value: "yearly" },
                            { label: "Custom Months", value: "custom" },
                        ]}
                    />
                    {errors?.frequency && <InputError message={errors.frequency} />}

                    {/* Interval */}
                    <Input
                        type="number"
                        min="1"
                        value={interval}
                        onChange={(e) => update("interval", e.target.value)}
                    />
                    {errors?.interval && <InputError message={errors.interval} />}

                    {/* Custom months */}
                    {frequency === "custom" && (
                        <MonthSelector
                            selected={months}
                            onChange={(m) => update("months", m)}
                        />
                    )}
                </>
            )}
        </div>
    );
}

export default ReccuringRuleFields;