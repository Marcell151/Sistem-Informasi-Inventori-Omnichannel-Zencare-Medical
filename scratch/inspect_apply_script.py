with open("scratch/apply_business_uat_to_bab4.py", "r", encoding="utf-8") as f:
    c = f.read()

print("Length:", len(c))
print("First 500 chars:")
print(c[:500])
print("\nLast 500 chars:")
print(c[-500:])

