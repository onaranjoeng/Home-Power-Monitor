WITH EnergyData AS (
    SELECT
        datetime,
        (julianday(datetime) - julianday(LAG(datetime) OVER (ORDER BY datetime))) AS seconds_CT1,
        (julianday(datetime) - julianday(LAG(datetime) OVER (ORDER BY datetime))) AS seconds_CT2, 
        CASE 
            WHEN real_power_CT1 < 0 or real_power_CT1 like 'NaN' THEN 0
            ELSE real_power_CT1
        END AS energy_watts_CT1,
        CASE 
            WHEN real_power_CT2 < 0 or real_power_CT2 like 'NaN' THEN 0
            ELSE real_power_CT2
        END AS energy_watts_CT2
    FROM
        power_data
    WHERE 
        energy_watts_CT1 > 0
)
SELECT
    DATE(datetime) AS date,
    (SUM(CASE WHEN seconds_CT1 = 0 or seconds_CT1 is null THEN 1 ELSE seconds_CT1 END * energy_watts_CT1)/SUM(CASE WHEN energy_watts_CT1 = 0 THEN 0 ELSE CASE WHEN seconds_CT1 = 0 or seconds_CT1 is null THEN 1 ELSE seconds_CT1 END END)) *24 / 1000 AS total_energy_CT1_kWh,
    (SUM(CASE WHEN seconds_CT2 = 0 or seconds_CT2 is null THEN 1 ELSE seconds_CT2 END * energy_watts_CT2)/SUM(CASE WHEN energy_watts_CT2 = 0 THEN 0 ELSE CASE WHEN seconds_CT2 = 0 or seconds_CT2 is null THEN 1 ELSE seconds_CT2 END END)) *24 / 1000 AS total_energy_CT2_kWh
FROM
    EnergyData
GROUP BY
    DATE(datetime);


INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '03/28/2024');
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '03/29/2024');
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '03/30/2024');
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '03/31/2024');
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '04/01/2024');
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '04/02/2024');
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, '04/03/2024');


INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, datetime('now' , '-6 day'));
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1297.36857, 140.754542, datetime('now' , '-5 day'));
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1797.76857, 375.245458, datetime('now' , '-4 day'));
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1547.56857, 225.245458, datetime('now' , '-3 day'));
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1300.96857, 200.245458, datetime('now' , '-2 day'));
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1800.56857, 250.245458, datetime('now' , '-1 day'));
INSERT INTO power_data (real_power_CT1, real_power_CT2, datetime) VALUES (1550.86857, 230.245458, datetime('now'));



