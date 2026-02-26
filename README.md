# IT391
Team 2's semester long project for IT391.

# OCR Usage

Install Guide for OpenOCR: https://github.com/Topdu/OpenOCR/blob/main/docs/openocr.md#quick-start

**Running OpenOCR** -- output is non human readable and will need to be cleaned with clean.py
```sh
# With visualization
openocr --task ocr --input_path path/to/img --is_vis
```

**Example of Visualisation**
<img width="2430" height="1620" alt="image" src="https://github.com/user-attachments/assets/16cc6891-d906-4c7a-81ba-e3cc0d5f0f21" />

clean.py Usage
```sh
python3 clean.py system_results.txt
```
clean.py output
```
Shop-No S+ 319 North Street
Norma1
IL,61761
(309)452-7400
1604mgr@fheg.fo1lett.com
RedbirdSpiritShop.com
ITEM QTY PRICE TOTAL
MILKA OREO BROWNIE BAR3.50Z
030299550 1@ $3.49 $3.49 T
STARBUCKS 150Z DBL MOCHA COFF
011591729 1@ $3.99 $3.99 T
Subtotal $7.48
Total Sales Tax $0.59
Total $8.07
Credit $8.07
Card:Visa
Account:0927
Auth:413359
Application ID:a0000000980840
Application Name:US DEBIT
TVR:0000000000
IAD:1f42ff60a00000000010030273000000004
0OOOOOOOOOOOOOOOOOOOOOOO0OOOO
PAN SegNo.:00
Audit Trace No.:61098634
Verification:Signature
Capture Method:Wave
@6.250% $0.25 6.250 US-IL
$034 9.750 @9 750% 1O
```



# ScannoKart – Front-End Overview


**Authentication & Dashboard Flow**

The current front-end build of ScannoKart includes a fully functional login interface that transitions seamlessly into the main dashboard.

**Login Page**

Users are presented with a clean authentication screen.

The system verifies whether an account exists in the browser before allowing access.

Login attempts will fail if the account has not been created.

This prevents unauthorized access to the dashboard environment.

**Account Requirement**

A user must first create an account before logging in.

The application validates stored account credentials within the browser.

If no matching account exists, access is denied.

**Dashboard Transition**

Upon successful login, users are automatically redirected to the ScannoKart Main Dashboard.

The logged-in user’s name is displayed in the top-right corner of the dashboard interface.

This provides clear session visibility and confirms active authentication.
# Contributors

Adam C, Alex FP, Ethan Causa, William Slaughter, Jackson Newton, Juan Munoz 
