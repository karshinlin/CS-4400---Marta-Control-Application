
--FLOW REPORT
SELECT Name, C.NumPassengerStart AS  '# Passengers In', C.NumPassengersEnd AS  '# Passengers Out', (C.NumPassengerStart - C.NumPassengersEnd) as Flow, C.Revenue, IsTrain,
    FROM Station
        LEFT JOIN (
        (
            SELECT * FROM (

                SELECT StartsAt, SUM( Tripfare ) AS Revenue, COUNT( * ) AS NumPassengerStart
                FROM Trip
                GROUP BY StartsAt
                ) AS A

            LEFT JOIN (

                SELECT EndsAt, COUNT( * ) AS NumPassengersEnd
                FROM Trip
                WHERE EndsAt IS NOT NULL
                GROUP BY EndsAt) AS B
            ON A.StartsAt = B.EndsAt
            )

            UNION (

            SELECT * FROM (

                SELECT StartsAt, SUM( Tripfare ) AS Revenue, COUNT( * ) AS NumPassengerStart
                FROM Trip
                GROUP BY StartsAt
                ) AS A
            RIGHT JOIN (

                SELECT EndsAt, COUNT( * ) AS NumPassengersEnd
                FROM Trip
                WHERE EndsAt IS NOT NULL
                GROUP BY EndsAt) AS B
            ON A.StartsAt = B.EndsAt
            )
        ) AS C
        ON StopID = C.StartsAt WHERE C.Revenue IS NOT NULL

--PASSENGER_HOME: VIEW TRIPS IN PROGRESS FOR PASSENGER ALONG WITH BREEZECARD BALANCE AND NAMES OF START/END STATION
SELECT BreezecardNum, Tripfare, StartsName, EndsName, Value, BelongsTo FROM
    (SELECT A.*, Station.Name AS EndsName FROM
        (SELECT Trip.*, Station.Name as StartsName FROM Trip
            LEFT JOIN
        Station
            ON Trip.StartsAt=Station.StopID) AS A
        LEFT JOIN Station ON A.EndsAt = Station.StopID) AS B
    NATURAL LEFT JOIN Breezecard