import sys, json, os
 
def process():
    try:
        # Check if we are getting data from a file argument OR a pipe
        if len(sys.argv) > 1:
            # Running as: python3 clean.py system_results.txt
            with open(sys.argv[1], 'r') as f:
                raw_input = f.read()
        elif not sys.stdin.isatty():
            # Running as: cat system_results.txt | python3 clean.py
            raw_input = sys.stdin.read()
        else:
            print("Usage: python3 clean.py <filename> OR cat <filename> | python3 clean.py")
            return
 
        # Split the text if it contains the filename prefix (your specific format)
        json_str = raw_input.split('\t')[-1] 
        data = json.loads(json_str)
 
        # Geometry-based sorting (Y then X)
        data.sort(key=lambda x: (sum(p[1] for p in x['points']), sum(p[0] for p in x['points'])))
 
        output = []
        last_y = -1
        current_line = []
 
        for item in data:
            avg_y = sum(p[1] for p in item['points']) / 4
            # If the text is on roughly the same line (within 15px)
            if last_y == -1 or abs(avg_y - last_y) < 15:
                current_line.append(item['transcription'])
            else:
                output.append(" ".join(current_line))
                current_line = [item['transcription']]
            last_y = avg_y
        output.append(" ".join(current_line))
 
        print("\n".join(output))
 
    except Exception as e:
        print(f"Error: {e}")
 
if __name__ == "__main__":
    process()