import InputLabel from "../Utilities/InputLabel";
import Input from "../Utilities/Input";
import Dropdown from "../Utilities/Dropdown";
import InputError from "../Utilities/InputError";

const monthNames = [
    { num: 1, label: "Jan" },
    { num: 2, label: "Feb" },
    { num: 3, label: "Mar" },
    { num: 4, label: "Apr" },
    { num: 5, label: "May" },
    { num: 6, label: "Jun" },
    { num: 7, label: "Jul" },
    { num: 8, label: "Aug" },
    { num: 9, label: "Sep" },
    { num: 10, label: "Oct" },
    { num: 11, label: "Nov" },
    { num: 12, label: "Dec" },
];

export default function RecurringRuleFields({ value, onChange, errors = {} }) {
    const update = (patch) => onChange({ ...value, ...patch });

    const frequencyOptions = [
        { label: "Monthly", value: "monthly" },
        { label: "Yearly", value: "yearly" },
        { label: "Custom Months", value: "custom" },
    ];

    const intervalLabel =
        value.frequency === "monthly"
            ? "Repeat every X months"
            : value.frequency === "yearly"
            ? "Repeat every X years"
            : "Repeat every X intervals";

    return (
        <div className="border rounded p-4 space-y-4 bg-gray-50">
            <InputLabel value="Recurring Rule" />

            {/* Toggle */}
            <div className="flex items-center gap-2">
                <Input
                    type="checkbox"
                    checked={value.isRecurring}
                    onChange={(e) => update({ isRecurring: e.target.checked })}
                    className="h-4 w-4"
                />
                <span className="text-gray-700">This transaction repeats</span>
            </div>

            {!value.isRecurring ? null : (
                <>
                    {/* Frequency */}
                    <div>
                        <Dropdown
                            label="Frequency"
                            value={frequencyOptions.find(
                                (opt) => opt.value === value.frequency
                            )}
                            onChange={(opt) => update({ frequency: opt.value })}
                            options={frequencyOptions}
                        />
                    </div>

                    {/* Interval */}
                    <div>
                        <InputLabel value={intervalLabel} />
                        <Input
                            type="number"
                            min="1"
                            value={value.interval}
                            onChange={(e) =>
                                update({ interval: Number(e.target.value) })
                            }
                        />
                        {errors.interval && (
                            <InputError message={errors.interval} />
                        )}
                    </div>

                    {/* Custom Months */}
                    {value.frequency === "custom" && (
                        <div>
                            <InputLabel value="Select months this occurs" />

                            <div className="grid grid-cols-4 gap-2 mt-2">
                                {monthNames.map((m) => (
                                    <label
                                        key={m.num}
                                        className="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={value.months.includes(
                                                m.num
                                            )}
                                            onChange={(e) => {
                                                const newMonths = e.target
                                                    .checked
                                                    ? [...value.months, m.num]
                                                    : value.months.filter(
                                                          (x) => x !== m.num
                                                      );
                                                update({ months: newMonths });
                                            }}
                                        />
                                        {m.label}
                                    </label>
                                ))}
                            </div>

                            {errors.months && (
                                <InputError message={errors.months} />
                            )}
                        </div>
                    )}
                </>
            )}
        </div>
    );
}